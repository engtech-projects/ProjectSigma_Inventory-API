<?php

namespace App\Http\Controllers;

use App\Models\TransactionMaterialReceiving;
use App\Http\Requests\UpdateTransactionMaterialReceivingRequest;
use App\Http\Resources\MaterialReceivingListingResource;
use App\Http\Resources\WarehouseTransactionResource;
use App\Models\SetupWarehouses;

class TransactionMaterialReceivingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $main = TransactionMaterialReceiving::latest()
        ->paginate(config('app.pagination.per_page', 10));
        return MaterialReceivingListingResource::collection($main)
        ->additional([
            'message' => 'Successfully fetched.',
            'success' => true,
        ]);
    }
    /**
     * Display the specified resource.
     */
    public function show(TransactionMaterialReceiving $resource)
    {
        $resource->load('items');
        return response()->json([
            "message" => "Successfully fetched.",
            "success" => true,
            "data" => new WarehouseTransactionResource($resource)
        ]);
    }

    /**
     * Update the specified resource in storage. FOR PETTY CASH TRANSACTION ONLY
     */
    public function update(UpdateTransactionMaterialReceivingRequest $request, TransactionMaterialReceiving $resource)
    {
        if (!$resource->isPettyCash) {
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

    public function transactionsByWarehouse(SetupWarehouses $warehouse)
    {
        $main = TransactionMaterialReceiving::where('warehouse_id', $warehouse->id)->latest()
        ->paginate(config('app.pagination.per_page', 10));
        return MaterialReceivingListingResource::collection($main)
        ->additional([
            'message' => 'Successfully fetched.',
            'success' => true,
        ]);
    }
}
