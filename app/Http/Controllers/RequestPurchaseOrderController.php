<?php

namespace App\Http\Controllers;

use App\Enums\PurchaseOrderProcessingStatus;
use App\Http\Requests\UpdatePurchaseProcessingStatusRequest;
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
        $requestPurchaseOrders = RequestPurchaseOrder::paginate(config('app.pagination.per_page', 15));
        return RequestPurchaseOrderListingResource::collection($requestPurchaseOrders)
        ->additional([
            'message' => 'Request Purchase Orders retrieved successfully.',
            'success' => true,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(RequestPurchaseOrder $resource)
    {
        return (new RequestPurchaseOrderDetailedResource($resource))
            ->additional([
                'message' => 'Request Purchase Order retrieved successfully.',
                'success' => true,
            ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequestPurchaseOrderRequest $request, RequestPurchaseOrder $resource)
    {
        $validatedData = $request->validated();
        $resource->update($validatedData);
        return (new RequestPurchaseOrderDetailedResource($resource))
            ->additional([
                'message' => 'Request Purchase Order updated successfully.',
                'success' => true,
            ]);
    }

    public function updateProcessingStatus(UpdatePurchaseProcessingStatusRequest $request, RequestPurchaseOrder $requestPurchaseOrder)
    {
        $newStatus = PurchaseOrderProcessingStatus::from($request->validated('processing_status'));
        $requestPurchaseOrder->update([
            'processing_status' => $newStatus,
        ]);
        return (new RequestPurchaseOrderDetailedResource($requestPurchaseOrder))
            ->additional([
                'message' => 'Purchase Order status updated successfully to ' . $newStatus->value,
                'success' => true,
            ]);
    }
}
