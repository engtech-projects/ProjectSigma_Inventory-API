<?php

namespace App\Http\Services;

use App\Models\RequestNCPO;
use App\Models\RequestPurchaseOrder;
use App\Models\RequestSupplier;
use App\Notifications\RequestNCPOForApprovalNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NcpoService
{
    public static function createNcpo($purchaseOrder)
    {
        return DB::transaction(function () use ($purchaseOrder) {
            $ncpoNo = RequestNCPO::generateReferenceNumber(
                'ncpo_no',
                fn ($prefix, $datePart, $number) => "{$prefix}-{$datePart}-{$number}",
                ['prefix' => 'NCPO', 'dateFormat' => 'Y-m']
            );
            $ncpo = RequestNCPO::create([
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
                    'changed_unit_price'  => $item->unit_price,
                    'changed_supplier_id' => $purchaseOrder->supplier_id,
                    'request_ncpo_id'     => $ncpo->id,
                ])->toArray();
            $ncpo->items()->createMany($items);
            if ($ncpo->getNextPendingApproval()) {
                $ncpo->notify(new RequestNCPOForApprovalNotification(request()->bearerToken(), $ncpo));
            }
            return $ncpo->load('items');
        });
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
        return $canvassItems->map(function ($canvassItem) use ($purchaseOrder) {
            $reqSlipItem = $canvassItem->requisitionSlipItem;
            $originalSupplier = [
                'id' => $purchaseOrder->supplier_id,
                'name' => $purchaseOrder->supplier?->company_name,
                'address' => $purchaseOrder->supplier?->company_address,
                'contact_number' => $purchaseOrder->supplier?->company_contact_number,
            ];
            $original = [
                'item_description' => $canvassItem->itemProfile->item_description ?? null,
                'specification'    => $reqSlipItem?->specification,
                'quantity'         => $reqSlipItem?->quantity,
                'uom_id'           => $reqSlipItem?->unit,
                'brand'            => $reqSlipItem?->preferred_brand,
                'unit_price'       => $canvassItem->unit_price,
                'total_amount'     => ($reqSlipItem?->quantity ?? 0) * ($canvassItem->unit_price ?? 0),
                'net_vat'          => $canvassItem->net_vat,
                'input_vat'        => $canvassItem->input_vat,
                'supplier'         => $originalSupplier,
            ];
            $latestChange = $purchaseOrder->ncpos
                ->flatMap->items
                ->where('item_id', $canvassItem->item_id)
                ->sortBy('created_at')
                ->last();
            if (!$latestChange) {
                return [
                    'item_id'  => $canvassItem->item_id,
                    'original' => $original,
                    'ncpo'     => null,
                ];
            }
            $supplierDetails = $this->getSupplierDetails($purchaseOrder, $latestChange);
            $changed = [
                'item_description' => $latestChange->changed_item_description ?? $original['item_description'],
                'specification'    => $latestChange->changed_specification ?? $original['specification'],
                'quantity'         => $latestChange->changed_qty ?? $original['quantity'],
                'uom_id'           => $latestChange->changed_uom_id ?? $original['uom_id'],
                'brand'            => $latestChange->changed_brand ?? $original['brand'],
                'unit_price'       => $latestChange->changed_unit_price ?? $original['unit_price'],
                'total_amount'     => ($latestChange->changed_qty ?? $original['quantity'])
                                    * ($latestChange->changed_unit_price ?? $original['unit_price']),
                'net_vat'          => $latestChange->net_vat,
                'input_vat'        => $latestChange->input_vat,
                'supplier'         => $supplierDetails['changed'] ?? $originalSupplier,
            ];
            $hasAnyChange = (
                $changed['item_description'] !== $original['item_description'] ||
                $changed['specification']    !== $original['specification'] ||
                $changed['brand']            !== $original['brand'] ||
                $changed['quantity']         !== $original['quantity'] ||
                $changed['uom_id']           !== $original['uom_id'] ||
                $changed['unit_price']       !== $original['unit_price'] ||
                $supplierDetails['changed'] !== null
            );
            return [
                'item_id'  => $canvassItem->item_id,
                'original' => $original,
                'ncpo'     => $hasAnyChange ? $changed : null,
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
