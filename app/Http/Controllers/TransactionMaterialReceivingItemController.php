<?php

namespace App\Http\Controllers;

use App\Enums\ServeStatus;
use App\Http\Requests\TransactionMaterialReceivingItemAcceptAllRequest;
use App\Http\Requests\TransactionMaterialReceivingItemAcceptSomeRequest;
use App\Http\Requests\TransactionMaterialReceivingItemRejectRequest;
use App\Models\TransactionMaterialReceivingItem;
use App\Http\Requests\UpdateTransactionMaterialReceivingItemRequest;

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
        $resource->quantity = $resource->requested_quantity;
        $resource->serve_status = ServeStatus::SERVED;
        $resource->save();
        // CREATE A WAREHOUSESTOCKTRANSACTION
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
        if($resource->transactionMaterialReceiving->isPettyCash && ($resource->specification == null || $resource->actual_brand_purchased == null || $resource->unit_price == null)) {
            return response()->json([
                'message' => 'Item detaills has not been updated yet.',
                'success' => false,
                'data' => $resource
            ]);
        }
        $resource->quantity = $validatedData['quantity'];
        $resource->remarks = $validatedData['remarks'];
        $resource->serve_status = ServeStatus::SERVED;
        $resource->save();
        // CREATE A WAREHOUSESTOCKTRANSACTION
        // TO BE UPDATED LATER FOR TRANSFER TO RETURN ITEMS FOR THE NOT ACCEPTED ITEMS
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
        $resource->serve_status = ServeStatus::SERVED;
        $resource->save();
        // TO BE UPDATED LATER FOR TRANSFER TO RETURN ITEMS
    }
}
