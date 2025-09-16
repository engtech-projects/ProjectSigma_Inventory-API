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
            'requestCanvassSummary.items.itemProfile',
            'requestCanvassSummary.items.requisitionSlipItem',
            'ncpos.items',
            'supplier',
        ]);
        $canvassItems = $purchaseOrder->requestCanvassSummary->items;
        $latestChanges = $purchaseOrder->ncpos
            ->flatMap(fn ($ncpo) => $ncpo->items ?? collect())
            ->sortByDesc('created_at')
            ->groupBy('item_id')
            ->map(fn ($items) => $items->first());
        $originalSupplier = [
            'id'             => $purchaseOrder->supplier_id,
            'name'           => $purchaseOrder->supplier?->company_name,
            'address'        => $purchaseOrder->supplier?->company_address,
            'contact_number' => $purchaseOrder->supplier?->company_contact_number,
        ];
        return $canvassItems->map(function ($canvassItem) use ($purchaseOrder, $latestChanges, $originalSupplier) {
            $reqSlipItem = $canvassItem->requisitionSlipItem;
            $latestChange = $latestChanges->get($canvassItem->item_id);
            $current = [
                'item_description' => $this->fallback($latestChange?->changed_item_description, $canvassItem->itemProfile?->item_description),
                'specification'    => $this->fallback($latestChange?->changed_specification, $reqSlipItem?->specification),
                'quantity'         => $this->fallback($latestChange?->changed_qty, $reqSlipItem?->quantity),
                'uom_id'           => $this->fallback($latestChange?->changed_uom_id, $reqSlipItem?->unit),
                'brand'            => $this->fallback($latestChange?->changed_brand, $reqSlipItem?->preferred_brand),
                'unit_price'       => $this->fallback($latestChange?->changed_unit_price, $canvassItem->unit_price),
                'total_amount'     => $this->fallback($latestChange?->changed_qty, $reqSlipItem?->quantity ?? 0)
                                     * $this->fallback($latestChange?->changed_unit_price, $canvassItem->unit_price ?? 0),
                'net_vat'          => $this->fallback($latestChange?->net_vat, $canvassItem->net_vat),
                'input_vat'        => $this->fallback($latestChange?->input_vat, $canvassItem->input_vat),
                'supplier'         => $latestChange
                    ? ($this->getSupplierDetails($purchaseOrder, $latestChange)['changed'] ?? $originalSupplier)
                    : $originalSupplier,
            ];

            return [
                'item_id' => $canvassItem->item_id,
                'current' => $current,
            ];
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
