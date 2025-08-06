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
            ]);
        }
        if($resource->transactionMaterialReceiving->isPettyCash) {
            if ($resource->transactionMaterialReceiving->supplier_id == null) {
                return response()->json([
                    'message' => 'Supplier has not been updated yet.',
                    'success' => false,
                    'data' => $resource
                ]);
            }
            if ($resource->transactionMaterialReceiving->reference == null) {
                return response()->json([
                    'message' => 'Reference has not been updated yet.',
                    'success' => false,
                    'data' => $resource
                ]);
            }
            if ($resource->transactionMaterialReceiving->terms_of_payment == null) {
                return response()->json([
                    'message' => 'Terms of payment have not been updated yet.',
                    'success' => false,
                    'data' => $resource
                ]);
            }
            if ($resource->transactionMaterialReceiving->particulars == null) {
                return response()->json([
                    'message' => 'Particulars have not been updated yet.',
                    'success' => false,
                    'data' => $resource
                ]);
            }
            if ($resource->specification == null) {
                return response()->json([
                    'message' => 'Item specification has not been updated yet.',
                    'success' => false,
                    'data' => $resource
                ]);
            }
            if ($resource->actual_brand_purchase == null) {
                return response()->json([
                    'message' => 'Actual brand purchased has not been updated yet.',
                    'success' => false,
                    'data' => $resource
                ]);
            }
            if ($resource->unit_price == null) {
                return response()->json([
                    'message' => 'Unit price has not been updated yet.',
                    'success' => false,
                    'data' => $resource
                ]);
            }
        }
        DB::transaction(function () use ($resource) {
            $resource->quantity = $resource->requested_quantity;
            $resource->acceptance_status = ReceivingAcceptanceStatus::ACCEPTED->value;
            $resource->serve_status = ServeStatus::SERVED;
            $resource->save();
            $resource->transactionMaterialReceiving->warehouseStockTransactions()->create([
                'warehouse_id' => $resource->transactionMaterialReceiving->warehouse_id,
                'type' => StockTransactionTypes::STOCKIN,
                'item_id' => $resource->item_id,
                'quantity' => $resource->quantity,
                'uom_id' => $resource->uom_id,
                'metadata' => [
                    'is_petty_cash' => $resource->transactionMaterialReceiving->isPettyCash
                ]
            ]);
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
            ]);
        }
        if($resource->transactionMaterialReceiving->isPettyCash) {
            if ($resource->transactionMaterialReceiving->supplier_id == null) {
                return response()->json([
                    'message' => 'Supplier has not been updated yet.',
                    'success' => false,
                    'data' => $resource
                ]);
            }
            if ($resource->transactionMaterialReceiving->reference == null) {
                return response()->json([
                    'message' => 'Reference has not been updated yet.',
                    'success' => false,
                    'data' => $resource
                ]);
            }
            if ($resource->transactionMaterialReceiving->terms_of_payment == null) {
                return response()->json([
                    'message' => 'Terms of payment have not been updated yet.',
                    'success' => false,
                    'data' => $resource
                ]);
            }
            if ($resource->transactionMaterialReceiving->particulars == null) {
                return response()->json([
                    'message' => 'Particulars have not been updated yet.',
                    'success' => false,
                    'data' => $resource
                ]);
            }
            if ($resource->specification == null) {
                return response()->json([
                    'message' => 'Item specification has not been updated yet.',
                    'success' => false,
                    'data' => $resource
                ]);
            }
            if ($resource->actual_brand_purchase == null) {
                return response()->json([
                    'message' => 'Actual brand purchased has not been updated yet.',
                    'success' => false,
                    'data' => $resource
                ]);
            }
            if ($resource->unit_price == null) {
                return response()->json([
                    'message' => 'Unit price has not been updated yet.',
                    'success' => false,
                    'data' => $resource
                ]);
            }
        }
        DB::transaction(function () use ($resource, $validatedData) {
            $resource->quantity = $validatedData['quantity'];
            $resource->remarks = $validatedData['remarks'];
            $resource->acceptance_status = ReceivingAcceptanceStatus::ACCEPTED->value;
            $resource->serve_status = ServeStatus::SERVED;
            $resource->save();
            $resource->transactionMaterialReceiving->warehouseStockTransactions()->create([
                'warehouse_id' => $resource->transactionMaterialReceiving->warehouse_id,
                'type' => StockTransactionTypes::STOCKIN,
                'item_id' => $resource->item_id,
                'quantity' => $resource->quantity,
                'uom_id' => $resource->uom_id,
                'metadata' => [
                    'is_petty_cash' => $resource->transactionMaterialReceiving->isPettyCash
                ]
            ]);
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
            ]);
        }
        if($resource->transactionMaterialReceiving->isPettyCash && ($resource->specification == null || $resource->actual_brand_purchased == null || $resource->unit_price == null)) {
            return response()->json([
                'message' => 'Item detaills has not been updated yet.',
                'success' => false,
                'data' => $resource
            ]);
        }
        $resource->quantity = 0;
        $resource->remarks = $validatedData['remarks'];
        $resource->acceptance_status = ReceivingAcceptanceStatus::REJECTED->value;
        $resource->serve_status = ServeStatus::SERVED;
        $resource->save();
        // TO BE UPDATED LATER FOR TRANSFER TO RETURN ITEMS
        return response()->json([
            'message' => 'Successfully Rejected.',
            'success' => true,
            'data' => $resource->refresh(),
        ]);
    }
}
