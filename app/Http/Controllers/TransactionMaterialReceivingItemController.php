<?php

namespace App\Http\Controllers;

use App\Enums\ReceivingAcceptanceStatus;
use App\Enums\ServeStatus;
use App\Enums\StockTransactionTypes;
use App\Http\Requests\TransactionMaterialReceivingItemAcceptAllRequest;
use App\Http\Requests\TransactionMaterialReceivingItemAcceptSomeRequest;
use App\Http\Requests\TransactionMaterialReceivingItemRejectRequest;
use App\Models\TransactionMaterialReceivingItem;
use App\Http\Requests\UpdateTransactionMaterialReceivingItemRequest;
use App\Models\RequestTurnover;
use App\Models\RequestTurnoverItems;
use App\Models\WarehouseStockTransactions;
use Illuminate\Support\Facades\DB;

class TransactionMaterialReceivingItemController extends Controller
{
    // To Update Specification, Actual Brand Purchased, and Unit Price. Only for Petty Cash Material Receiving
    public function update(UpdateTransactionMaterialReceivingItemRequest $request, TransactionMaterialReceivingItem $resource)
    {
        if (!$resource->transactionMaterialReceiving->isPettyCash) {
            return response()->json([
                'message' => 'Cannot update non petty cash Material Receiving.',
                'success' => false,
            ], 400);
        }
        $resource->update($request->validated());
        return response()->json([
            'message' => 'Successfully updated.',
            'success' => true,
            'data' => $resource->refresh(),
        ]);
    }
    public function acceptAll(TransactionMaterialReceivingItem $resource, TransactionMaterialReceivingItemAcceptAllRequest $request)
    {
        $request->validated();

        if ($resource->is_processed) {
            return response()->json(['message' => 'Item has already been processed.', 'success' => false], 400);
        }

        if ($resource->transactionMaterialReceiving->isPettyCash) {
            if ($resp = $this->ensurePettyCashHeadersComplete($resource)) {
                return $resp;
            }
        }

        $mrr = $resource->transactionMaterialReceiving;
        $fromWarehouseId = $mrr->metadata['from_warehouse_id'] ?? null;

        $qtyToAccept = $resource->requested_quantity
            - $mrr->warehouseStockTransactions()
                ->where('item_id', $resource->item_id)
                ->where('type', StockTransactionTypes::STOCKIN->value)
                ->sum('quantity');

        if ($qtyToAccept <= 0) {
            return response()->json(['message' => 'No quantity left to accept.', 'success' => false], 400);
        }

        DB::transaction(function () use ($resource, $mrr, $qtyToAccept, $fromWarehouseId) {
            // Update MRR Item status
            $resource->acceptance_status = ReceivingAcceptanceStatus::ACCEPTED->value;
            $resource->serve_status = $mrr->is_ncpo ? ServeStatus::UNSERVED->value : ServeStatus::SERVED->value;
            $resource->save();

            // 1. STOCK IN → Destination Warehouse
            $mrr->warehouseStockTransactions()->create([
                'warehouse_id' => $mrr->warehouse_id,
                'type'         => StockTransactionTypes::STOCKIN->value,
                'item_id'      => $resource->item_id,
                'quantity'     => $qtyToAccept,
                'uom_id'       => $resource->uom_id,
                'referenceable_type' => get_class($mrr),
                'referenceable_id'   => $mrr->id,
                'metadata'     => [
                    'is_turnover'       => true,
                    'from_warehouse_id' => $fromWarehouseId,
                    'rt_id'             => $mrr->metadata['rt_id'] ?? null,
                    'full_acceptance'   => true,
                    'accepted_qty'      => $qtyToAccept,
                ],
            ]);

            // 2. STOCK OUT → Source Warehouse (only if turnover)
            if ($fromWarehouseId) {
                WarehouseStockTransactions::create([
                    'warehouse_id'       => $fromWarehouseId,
                    'type'               => StockTransactionTypes::STOCKOUT->value,
                    'item_id'            => $resource->item_id,
                    'quantity'           => $qtyToAccept,
                    'uom_id'             => $resource->uom_id,
                    'referenceable_type' => get_class($mrr),
                    'referenceable_id'   => $mrr->id,
                    'metadata'           => [
                        'is_turnover'           => true,
                        'to_warehouse_id'       => $mrr->warehouse_id,
                        'rt_id'                 => $mrr->metadata['rt_id'] ?? null,
                        'full_acceptance'       => true,
                        'accepted_qty'          => $qtyToAccept,
                        'notes'                 => 'Turnover items accepted at destination',
                        'time'                  => now()->toDateTimeString(),
                    ],
                ]);
            }

            $this->syncRequestTurnoverFromMrrItem($resource);
        });

        return response()->json([
            'message' => "Successfully accepted {$qtyToAccept} items.",
            'success' => true,
            'data'    => $resource->refresh(),
        ]);
    }

    public function acceptSome(TransactionMaterialReceivingItem $resource, TransactionMaterialReceivingItemAcceptSomeRequest $request)
    {
        $validatedData = $request->validated();

        if ($resource->is_processed) {
            return response()->json(['message' => 'Item has already been processed.', 'success' => false], 400);
        }

        $qtyToAccept = $validatedData['quantity'];
        $mrr = $resource->transactionMaterialReceiving;
        $fromWarehouseId = $mrr->metadata['from_warehouse_id'] ?? null;

        $totalAcceptedSoFar = $mrr->warehouseStockTransactions()
            ->where('item_id', $resource->item_id)
            ->where('type', StockTransactionTypes::STOCKIN->value)
            ->sum('quantity');

        if (($totalAcceptedSoFar + $qtyToAccept) > $resource->requested_quantity) {
            return response()->json(['message' => 'Cannot accept more than requested quantity.', 'success' => false], 400);
        }

        if ($qtyToAccept <= 0) {
            return response()->json(['message' => 'Quantity must be greater than zero.', 'success' => false], 400);
        }

        DB::transaction(function () use ($resource, $mrr, $qtyToAccept, $validatedData, $fromWarehouseId) {
            // Update status
            $resource->acceptance_status = ReceivingAcceptanceStatus::ACCEPTED->value;
            $resource->serve_status = $mrr->is_ncpo ? ServeStatus::UNSERVED->value : ServeStatus::SERVED->value;
            $resource->remarks = $validatedData['remarks'] ?? $resource->remarks;
            $resource->save();

            // 1. STOCK IN → Destination
            $mrr->warehouseStockTransactions()->create([
                'warehouse_id' => $mrr->warehouse_id,
                'type'         => StockTransactionTypes::STOCKIN->value,
                'item_id'      => $resource->item_id,
                'quantity'     => $qtyToAccept,
                'uom_id'       => $resource->uom_id,
                'referenceable_type' => get_class($mrr),
                'referenceable_id'   => $mrr->id,
                'metadata'     => [
                    'is_turnover'        => true,
                    'from_warehouse_id'  => $fromWarehouseId,
                    'rt_id'              => $mrr->metadata['rt_id'] ?? null,
                    'partial_acceptance' => true,
                    'accepted_qty'       => $qtyToAccept,
                    'mrr_item_id'        => $resource->id,
                ],
            ]);

            // 2. STOCK OUT → Source Warehouse
            if ($fromWarehouseId) {
                WarehouseStockTransactions::create([
                    'warehouse_id'       => $fromWarehouseId,
                    'type'               => StockTransactionTypes::STOCKOUT->value,
                    'item_id'            => $resource->item_id,
                    'quantity'           => $qtyToAccept,
                    'uom_id'             => $resource->uom_id,
                    'referenceable_type' => get_class($mrr),
                    'referenceable_id'   => $mrr->id,
                    'metadata'           => [
                        'is_turnover'           => true,
                        'to_warehouse_id'       => $mrr->warehouse_id,
                        'rt_id'                 => $mrr->metadata['rt_id'] ?? null,
                        'partial_acceptance'    => true,
                        'accepted_qty'          => $qtyToAccept,
                        'mrr_item_id'           => $resource->id,
                        'notes'                 => 'Partial turnover accepted',
                    ],
                ]);
            }

            $this->syncRequestTurnoverFromMrrItem($resource);
        });

        return response()->json([
            'message' => "Successfully accepted {$qtyToAccept} items.",
            'success' => true,
            'data'    => $resource->refresh(),
        ]);
    }
    public function reject(TransactionMaterialReceivingItem $resource, TransactionMaterialReceivingItemRejectRequest $request)
    {
        $validatedData = $request->validated();
        if($resource->is_processed) {
            return response()->json([
                'message' => 'Item has already been processed.',
                'success' => false,
                'data' => $resource
            ], 400);
        }
        $resource->quantity = 0;
        $resource->remarks = $validatedData['remarks'];
        $resource->acceptance_status = ReceivingAcceptanceStatus::REJECTED->value;
        $resource->serve_status = ServeStatus::UNSERVED->value;
        $resource->save();
        $this->syncRequestTurnoverFromMrrItem($resource);
        // TO BE UPDATED LATER FOR TRANSFER TO RETURN ITEMS
        return response()->json([
            'message' => 'Successfully Rejected.',
            'success' => true,
            'data' => $resource->refresh(),
        ]);
    }
    private function ensurePettyCashHeadersComplete(TransactionMaterialReceivingItem $resource)
    {
        $mrr = $resource->transactionMaterialReceiving;
        $checks = [
            [$mrr->supplier_id !== null, 'Supplier has not been updated yet.'],
            [$mrr->reference !== null, 'Reference has not been updated yet.'],
            [$mrr->terms_of_payment !== null, 'Terms of payment have not been updated yet.'],
            [$mrr->particulars !== null, 'Particulars have not been updated yet.'],
            [$resource->specification !== null, 'Item specification has not been updated yet.'],
            [$resource->actual_brand_purchase !== null, 'Actual brand purchased has not been updated yet.'],
            [$resource->unit_price !== null, 'Unit price has not been updated yet.'],
        ];
        foreach ($checks as [$ok, $message]) {
            if (!$ok) {
                return response()->json(['message' => $message, 'success' => false, 'data' => $resource], 400);
            }
        }
        return null;
    }
    private function syncRequestTurnoverFromMrrItem(TransactionMaterialReceivingItem $mrrItem)
    {
        $mrr = $mrrItem->transactionMaterialReceiving;
        $metadata = $mrr->metadata;
        if (!($metadata['is_turnover'] ?? false)) {
            return;
        }
        $rtId = $metadata['rt_id'] ?? null;
        if (!$rtId) {
            return;
        }
        $requestTurnover = RequestTurnover::find($rtId);
        if (!$requestTurnover) {
            return;
        }
        $rtItemId = data_get($mrrItem->metadata, 'rt_item_id');
        if (!$rtItemId) {
            return;
        }
        $rtItem = RequestTurnoverItems::find($rtItemId);
        if (!$rtItem) {
            return;
        }
        $newStatus = $mrrItem->acceptance_status === ReceivingAcceptanceStatus::ACCEPTED->value
            ? 'Accepted'
            : 'Denied';
        $rtItem->updateQuietly(['accept_status' => $newStatus]);
        $hasPending = $requestTurnover->items()
            ->where('accept_status', 'Pending')
            ->exists();
        if (!$hasPending) {
            $requestTurnover->update([
                'received_date' => now(),
                'received_name' => auth()->user()?->name,
            ]);
        }
    }
}
