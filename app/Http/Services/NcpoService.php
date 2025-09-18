<?php

namespace App\Http\Services;

use App\Enums\ServeStatus;
use App\Models\RequestNcpo;
use App\Models\RequestPurchaseOrder;
use App\Models\RequestSupplier;
use App\Models\TransactionMaterialReceiving;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NcpoService
{
    public static function createMrrFromNcpo(RequestNcpo $requestNcpo): TransactionMaterialReceiving
    {
        return DB::transaction(function () use ($requestNcpo) {
            $mrr = new TransactionMaterialReceiving();
            $mrr->warehouse_id      = $requestNcpo->purchaseOrder->warehouse_id;
            $mrr->reference_no      = TransactionMaterialReceiving::generateNewMrrReferenceNumber();
            $mrr->supplier_id       = $requestNcpo->items->first()->changed_supplier_id;
            $mrr->reference         = $requestNcpo->ncpo_no;
            $mrr->terms_of_payment  = $requestNcpo->purchaseOrder->terms_of_payment;
            $mrr->transaction_date  = $requestNcpo->date;
            $mrr->metadata          = [
                'is_ncpo' => true,
                'ncpo_id' => $requestNcpo->id,
                'rs_id' => $requestNcpo->purchaseOrder->rs_id,
            ];
            $mrr->save();
            $mappedItems = $requestNcpo->items->map(fn ($item) => [
                'transaction_material_receiving_id' => $mrr->id,
                'item_id'              => $item->item_id,
                'specification'        => $item->changed_specification,
                'actual_brand_purchase' => $item->changed_brand,
                'requested_quantity'   => $item->changed_qty,
                'quantity'             => $item->changed_qty,
                'uom_id'               => $item->changed_uom_id,
                'unit_price'           => $item->changed_unit_price,
                'serve_status'         => ServeStatus::UNSERVED,
                'remarks'              => $item->remarks,
                'metadata'             => [
                    'cancel_item' => $item->cancel_item ?? false,
                    'ncpo_id' => $requestNcpo->id,
                    'ncpo_item_ids' => $requestNcpo->items->pluck('id')->toArray(),
                ],
            ]);
            $mrr->items()->createMany($mappedItems->toArray());
            return $mrr;
        });
    }
    private function fallback($primary, $fallback)
    {
        return ($primary !== null && $primary !== '') ? $primary : $fallback;
    }

    public function getItemsWithChanges(RequestPurchaseOrder $purchaseOrder): Collection
    {
        $purchaseOrder->load([
            'ncpos.items',
        ]);
        $metadata = $purchaseOrder->metadata ?? [];
        $items = collect($metadata['items'] ?? []);
        $originalSupplier = $metadata['supplier'] ?? [
            'id' => null,
            'name' => null,
            'address' => null,
            'contact_number' => null,
        ];
        $allChanges = $purchaseOrder->ncpos
            ->flatMap(fn ($ncpo) => $ncpo->items ?? collect())
            ->sortByDesc('created_at')
            ->groupBy('item_id')
            ->map(fn ($items) => $items->first());
        $approvedChanges = $purchaseOrder->ncpos
            ->filter(fn ($ncpo) => strtolower($ncpo->request_status) === 'approved')
            ->flatMap(fn ($ncpo) => $ncpo->items ?? collect())
            ->sortByDesc('created_at')
            ->groupBy('item_id')
            ->map(fn ($items) => $items->first());
        return $items->map(function ($item) use ($purchaseOrder, $allChanges, $approvedChanges, $originalSupplier) {
            $approvedChange = $approvedChanges->get($item['item_id']);
            $latestChange = $allChanges->get($item['item_id']);
            $original = [
                'item_description' => $item['item_description'] ?? null,
                'specification' => $item['specification'] ?? null,
                'quantity' => $item['quantity'] ?? null,
                'uom' => $item['uom'] ?? null,
                'actual_brand' => $item['actual_brand_purchase'] ?? null,
                'unit_price' => number_format($item['unit_price'] ?? 0, 2),
                'total_amount' => number_format($item['net_amount'] ?? ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0), 2),
                'net_vat' => number_format($item['net_vat'] ?? 0, 2),
                'input_vat' => number_format($item['input_vat'] ?? 0, 2),
                'supplier_name' => $originalSupplier['name'],
                'supplier_address' => $originalSupplier['address'],
                'supplier_contact_number' => $originalSupplier['contact_number'],
            ];
            $result = [
                'item_id' => $item['item_id'],
                'original' => $original,
            ];
            if ($latestChange) {
                if ($approvedChange) {
                    $change = $approvedChange;
                    $result['changed'] = [
                        'item_description' => $this->fallback($change->changed_item_description, $original['item_description']),
                        'specification' => $this->fallback($change->changed_specification, $original['specification']),
                        'quantity' => $this->fallback($change->changed_qty, $original['quantity']),
                        'uom' => $change->changed_uom ? $change->changed_uom->name : $original['uom'],
                        'actual_brand' => $this->fallback($change->changed_brand, $original['actual_brand']),
                        'unit_price' => number_format($this->fallback($change->changed_unit_price, $original['unit_price']), 2),
                        'total_amount' => number_format($this->fallback($change->changed_qty, $original['quantity'] ?? 0)
                            * $this->fallback($change->changed_unit_price, $original['unit_price'] ?? 0), 2),
                        'net_vat' => number_format($this->fallback($change->net_vat, $original['net_vat']), 2),
                        'input_vat' => number_format($this->fallback($change->input_vat, $original['input_vat']), 2),
                        'supplier_name' => $this->getSupplierDetails($purchaseOrder, $change)['changed']['name'] ?? $originalSupplier['name'],
                        'supplier_address' => $this->getSupplierDetails($purchaseOrder, $change)['changed']['address'] ?? $originalSupplier['address'],
                        'supplier_contact_number' => $this->getSupplierDetails($purchaseOrder, $change)['changed']['contact_number'] ?? $originalSupplier['contact_number'],
                    ];
                } else {
                    $result['changed'] = 'Pending Approval';
                }
            }
            return $result;
        });
    }

    public function getSupplierDetails($purchaseOrder, $latestChange = null): array
    {
        $original = [
            'id' => $purchaseOrder->supplier_id,
            'name' => $purchaseOrder->supplier?->company_name,
            'address' => $purchaseOrder->supplier?->company_address,
            'contact_number' => $purchaseOrder->supplier?->company_contact_number,
        ];
        if (!$latestChange || !$latestChange->changed_supplier_id) {
            return $original;
        }
        $changedSupplier = RequestSupplier::find($latestChange->changed_supplier_id);
        $changed = [
            'id' => $changedSupplier?->id,
            'name' => $changedSupplier?->company_name,
            'address' => $changedSupplier?->company_address,
            'contact_number' => $changedSupplier?->company_contact_number,
        ];
        if ($changed['id'] === $original['id']) {
            return [
                'original' => $original,
            ];
        }
        return [
            'original' => $original,
            'changed'  => $changed,
        ];
    }
}
