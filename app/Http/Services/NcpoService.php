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
            $mappedItems = $requestNcpo->items->map(fn($item) => [
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
        $purchaseOrder->load('ncpos.items');
        $metadata = $purchaseOrder->metadata ?? [];
        $items = collect($metadata['items'] ?? []);
        $allChanges = $purchaseOrder->ncpos
            ->flatMap(fn($ncpo) => $ncpo->items ?? collect())
            ->sortByDesc('created_at')
            ->groupBy('item_id')
            ->map(fn($group) => $group->first());
        $approvedChanges = $purchaseOrder->ncpos
            ->filter(fn($ncpo) => strtolower($ncpo->request_status) === 'approved')
            ->flatMap(fn($ncpo) => $ncpo->items ?? collect())
            ->sortByDesc('created_at')
            ->groupBy('item_id')
            ->map(fn($group) => $group->first());
        return $items->map(function ($item) use ($purchaseOrder, $allChanges, $approvedChanges) {
            $original = $this->mapOriginalItem($item, $purchaseOrder);
            $result = ['original' => $original];
            $change = $approvedChanges->get($item['item_id']) ?? $allChanges->get($item['item_id']);
            if ($change) {
                $result['changed'] = $this->mapChangedItem($original, $change, $purchaseOrder);
            }
            return $result;
        });
    }
    private function mapOriginalItem(array $item, RequestPurchaseOrder $purchaseOrder): array
    {
        return [
            'item_id'        => $item['item_id'],
            'item_description' => $item['item_description'] ?? null,
            'specification'  => $item['specification'] ?? null,
            'quantity'       => $item['quantity'] ?? null,
            'uom'            => $item['uom'] ?? null,
            'uom_id'         => $item['uom_id'] ?? null,
            'convertable_units' => $item['convertable_units'] ?? [],
            'actual_brand'   => $item['actual_brand_purchase'] ?? null,
            'unit_price'     => round((float)($item['unit_price'] ?? 0), 2),
            'total_amount'   => round((float)($item['net_amount'] ?? (($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0))), 2),
            'net_vat'        => round((float)($item['net_vat'] ?? 0), 2),
            'input_vat'      => round((float)($item['input_vat'] ?? 0), 2),
            'supplier_details' => $this->getSupplierDetails($purchaseOrder),
        ];
    }
    private function mapChangedItem(array $original, $change, RequestPurchaseOrder $purchaseOrder): array
    {
        return [
            'item_description' => $this->fallback($change->changed_item_description, $original['item_description']),
            'specification'    => $this->fallback($change->changed_specification, $original['specification']),
            'quantity'         => $this->fallback($change->changed_qty, $original['quantity']),
            'uom'              => $change->changed_uom?->name ?? $original['uom'],
            'uom_id'           => $change->changed_uom?->id ?? $original['uom_id'],
            'actual_brand'     => $this->fallback($change->changed_brand, $original['actual_brand']),
            'unit_price'       => round((float)($this->fallback($change->changed_unit_price, $original['unit_price']) ?? 0), 2),
            'total_amount'     => round((float)(
                $this->fallback($change->changed_qty, $original['quantity'] ?? 0) *
                $this->fallback($change->changed_unit_price, $original['unit_price'] ?? 0)
            ), 2),
            'net_vat'          => round((float)($this->fallback($change->net_vat, $original['net_vat']) ?? 0), 2),
            'input_vat'        => round((float)($this->fallback($change->input_vat, $original['input_vat']) ?? 0), precision: 2),
            'supplier_details' => $this->getSupplierDetails($purchaseOrder, $change, true),
        ];
    }
    public function getSupplierDetails($purchaseOrder, $latestChange = null, bool $isChanged = false): ?array
    {
        if ($isChanged && $latestChange?->changed_supplier_id) {
            $changedSupplier = RequestSupplier::find($latestChange->changed_supplier_id);
            return [
                'id'             => $changedSupplier?->id,
                'name'           => $changedSupplier?->company_name,
                'address'        => $changedSupplier?->company_address,
                'contact_number' => $changedSupplier?->company_contact_number,
            ];
        }
        return [
            'id'             => $purchaseOrder->supplier_id,
            'name'           => $purchaseOrder->supplier?->company_name,
            'address'        => $purchaseOrder->supplier?->company_address,
            'contact_number' => $purchaseOrder->supplier?->company_contact_number,
        ];
    }
}
