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
        if($resource->is_processed) {
            return response()->json([
                'message' => 'Item has already been processed.',
                'success' => false,
                'data' => $resource
            ], 400);
        }
        if($resource->transactionMaterialReceiving->isPettyCash) {
            if ($resp = $this->ensurePettyCashHeadersComplete($resource)) {
                return $resp;
            }
        }
        DB::transaction(function () use ($resource) {
            $resource->quantity = $resource->requested_quantity;
            $resource->acceptance_status = ReceivingAcceptanceStatus::ACCEPTED->value;
            if ($resource->transactionMaterialReceiving->is_ncpo) {
                $resource->serve_status = ServeStatus::UNSERVED->value;
            } else {
                $resource->serve_status = ServeStatus::SERVED->value;
            }
            $resource->save();
            $resource->transactionMaterialReceiving->warehouseStockTransactions()->create([
                'warehouse_id' => $resource->transactionMaterialReceiving->warehouse_id,
                'type' => StockTransactionTypes::STOCKIN->value,
                'item_id' => $resource->item_id,
                'quantity' => $resource->quantity,
                'uom_id' => $resource->uom_id,
                'metadata' => [
                    'is_petty_cash' => $resource->transactionMaterialReceiving->isPettyCash
                ]
            ]);
            $this->syncRequestTurnoverFromMrrItem($resource);
        });
        return response()->json([
            'message' => 'Successfully accepted.',
            'success' => true,
            'data' => $resource->refresh(),
        ]);
    }
    public function acceptSome(TransactionMaterialReceivingItem $resource, TransactionMaterialReceivingItemAcceptSomeRequest $request)
    {
        $validatedData = $request->validated();
        if($resource->is_processed) {
            return response()->json([
                'message' => 'Item has already been processed.',
                'success' => false,
                'data' => $resource
            ], 400);
        }
        if($resource->transactionMaterialReceiving->isPettyCash) {
            if ($resp = $this->ensurePettyCashHeadersComplete($resource)) {
                return $resp;
            }
        }
        DB::transaction(function () use ($resource, $validatedData) {
            $resource->quantity = $validatedData['quantity'];
            $resource->remarks = $validatedData['remarks'];
            $resource->acceptance_status = ReceivingAcceptanceStatus::ACCEPTED->value;
            if ($resource->transactionMaterialReceiving->is_ncpo) {
                $resource->serve_status = ServeStatus::UNSERVED->value;
            } else {
                $resource->serve_status = ServeStatus::SERVED->value;
            }
            $resource->save();
            $resource->transactionMaterialReceiving->warehouseStockTransactions()->create([
                'warehouse_id' => $resource->transactionMaterialReceiving->warehouse_id,
                'type' => StockTransactionTypes::STOCKIN->value,
                'item_id' => $resource->item_id,
                'quantity' => $resource->quantity,
                'uom_id' => $resource->uom_id,
                'metadata' => [
                    'is_petty_cash' => $resource->transactionMaterialReceiving->isPettyCash
                ]
            ]);
            $this->syncRequestTurnoverFromMrrItem($resource);
        });
        // TO BE UPDATED LATER FOR TRANSFER TO RETURN ITEMS FOR THE NOT ACCEPTED ITEMS
        return response()->json([
            'message' => 'Successfully accepted.',
            'success' => true,
            'data' => $resource->refresh(),
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
