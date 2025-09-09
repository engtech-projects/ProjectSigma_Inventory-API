<?php

namespace App\Http\Services;

use App\Models\RequestNcpo;
use App\Models\RequestPurchaseOrder;
use App\Models\RequestSupplier;
use App\Notifications\RequestNcpoForApprovalNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NcpoService
{
    public static function createNcpo($purchaseOrder)
    {
        return DB::transaction(function () use ($purchaseOrder) {
            $ncpoNo = RequestNcpo::generateReferenceNumber(
                'ncpo_no',
                fn ($prefix, $datePart, $number) => "{$prefix}-{$datePart}-{$number}",
                ['prefix' => 'NCPO', 'dateFormat' => 'Y-m']
            );
            $ncpo = RequestNcpo::create([
                'ncpo_no'       => $ncpoNo,
                'date'          => now(),
                'justification' => null,
                'po_id'         => $purchaseOrder->id,
                'created_by' => auth()->user()->id,
                'approvals'     => [],
            ]);
            $items = $purchaseOrder->requestCanvassSummary
                ->items()
                ->get()
                ->map(fn ($item) => [
                    'item_id'             => $item->item_id,
                    'changed_qty'         => $item->qty,
                    'changed_uom_id'      => $item->uom_id,
                    'changed_unit_price'  => null,
                    'changed_supplier_id' => null,
                    'request_ncpo_id'     => $ncpo->id,
                ])->toArray();
            $ncpo->items()->createMany($items);
            if ($ncpo->getNextPendingApproval()) {
                $ncpo->notify(new RequestNcpoForApprovalNotification(request()->bearerToken(), $ncpo));
            }
            return $ncpo->load('items');
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
