<?php

namespace App\Http\Controllers;

use App\Models\RequestPurchaseOrder;
use App\Http\Requests\UpdateRequestPurchaseOrderRequest;
use App\Http\Resources\RequestPurchaseOrderDetailedResource;
use App\Http\Resources\RequestPurchaseOrderListingResource;

class RequestPurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $requestPurchaseOrders = RequestPurchaseOrder::all();
        return RequestPurchaseOrderListingResource::collection($requestPurchaseOrders)
        ->additional([
            'message' => 'Request Purchase Orders retrieved successfully.',
            'success' => true,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(RequestPurchaseOrder $requestPurchaseOrder)
    {
        return response()->json([
            'message' => 'Request Purchase Order retrieved successfully.',
            'success' => true,
            'data' => new RequestPurchaseOrderDetailedResource($requestPurchaseOrder),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequestPurchaseOrderRequest $request, RequestPurchaseOrder $requestPurchaseOrder)
    {
        $validatedData = $request->validated();
        $requestPurchaseOrder->update($validatedData);
        return response()->json([
            'message' => 'Request Purchase Order updated successfully.',
            'success' => true,
            'data' => $requestPurchaseOrder,
        ]);
    }
}
