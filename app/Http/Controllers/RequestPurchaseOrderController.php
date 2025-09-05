<?php

namespace App\Http\Controllers;

use App\Enums\PurchaseOrderProcessingStatus;
use App\Http\Requests\UpdatePurchaseProcessingStatusRequest;
use App\Models\RequestPurchaseOrder;
use App\Http\Requests\UpdateRequestPurchaseOrderRequest;
use App\Http\Resources\RequestPurchaseOrderDetailedResource;
use App\Http\Resources\RequestPurchaseOrderItemsDetailedResource;
use App\Http\Resources\RequestPurchaseOrderListingResource;
use App\Http\Services\NcpoService;
use App\Http\Services\PurchaseOrderService;

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
        if ($newStatus === PurchaseOrderProcessingStatus::TURNED_OVER) {
            PurchaseOrderService::createMrrFromPurchaseOrder($requestPurchaseOrder);
        }
        return (new RequestPurchaseOrderDetailedResource($requestPurchaseOrder))
            ->additional([
                'message' => "Processing status updated to {$newStatus->value} successfully.",
                'success' => true,
            ]);
    }

    public function showDetailed(RequestPurchaseOrder $resource)
    {
        // Load necessary relationships for computing NCPO changes
        $resource->load([
            'requestCanvassSummary.items.itemProfile',
            'requestCanvassSummary.priceQuotation.requestProcurement.requisitionSlip.items',
            'ncpos.items',
            'supplier'
        ]);

        return (new RequestPurchaseOrderItemsDetailedResource($resource))
            ->additional([
                'message' => 'Detailed Purchase Order with computed values retrieved successfully.',
                'success' => true,
            ]);
    }

    /**
     * Get purchase order comparison (original vs computed)
     */
    public function getComparison(RequestPurchaseOrder $resource)
    {
        $ncpoService = app(NcpoService::class);
        $comparison = $ncpoService->getDetailedComparison($resource);

        return response()->json([
            'data' => $comparison,
            'message' => 'Purchase Order comparison retrieved successfully.',
            'success' => true,
        ]);
    }
}
