<?php

namespace App\Http\Controllers;

use App\Enums\PurchaseOrderProcessingStatus;
use App\Http\Requests\SearchPurchaseOrderRequest;
use App\Http\Requests\UpdatePurchaseProcessingStatusRequest;
use App\Models\RequestPurchaseOrder;
use App\Http\Requests\UpdateRequestPurchaseOrderRequest;
use App\Http\Resources\RequestPurchaseOrderDetailedResource;
use App\Http\Resources\RequestPurchaseOrderItemsDetailedResource;
use App\Http\Resources\RequestPurchaseOrderListingResource;
use App\Http\Services\PurchaseOrderService;

class RequestPurchaseOrderController extends Controller
{
    public function index()
    {
        $requestPurchaseOrders = RequestPurchaseOrder::paginate(config('app.pagination.per_page', 15));
        return RequestPurchaseOrderListingResource::collection($requestPurchaseOrders)
            ->additional([
                'message' => 'Request Purchase Orders retrieved successfully.',
                'success' => true,
            ]);
    }

    public function show(RequestPurchaseOrder $resource)
    {
        $resource->load([
            'ncpos',
            'supplier',
        ]);
        return RequestPurchaseOrderDetailedResource::make($resource)
            ->additional([
                'message' => 'Request Purchase Order retrieved successfully.',
                'success' => true,
            ]);
    }
    public function update(UpdateRequestPurchaseOrderRequest $request, RequestPurchaseOrder $resource)
    {
        $validatedData = $request->validated();
        $resource->update($validatedData);
        return RequestPurchaseOrderDetailedResource::make($resource)
            ->additional([
                'message' => 'Request Purchase Order updated successfully.',
                'success' => true,
            ]);
    }

    public function updateProcessingStatus(UpdatePurchaseProcessingStatusRequest $request, RequestPurchaseOrder $requestPurchaseOrder)
    {
        $newStatus = PurchaseOrderProcessingStatus::from($request->validated('processing_status'));
        if ($newStatus === PurchaseOrderProcessingStatus::TURNED_OVER) {
            PurchaseOrderService::createMrrFromPurchaseOrder($requestPurchaseOrder);
        }
        if ($newStatus === PurchaseOrderProcessingStatus::SERVED) {
            PurchaseOrderService::setServed($requestPurchaseOrder);
        } else {
            $requestPurchaseOrder->update([
                'processing_status' => $newStatus,
            ]);
        }
        return RequestPurchaseOrderDetailedResource::make($requestPurchaseOrder)
            ->additional([
                'message' => "Processing status updated to {$newStatus->value} successfully.",
                'success' => true,
            ]);
    }

    public function showDetailed(RequestPurchaseOrder $resource)
    {
        $resource->load([
            'requestCanvassSummary.items.itemProfile',
            'requestCanvassSummary.priceQuotation.requestProcurement.requisitionSlip.items',
            'ncpos.items',
            'ncpos'
        ]);
        return RequestPurchaseOrderItemsDetailedResource::make($resource)
            ->additional([
                'message' => 'Detailed Purchase Order with computed values retrieved successfully.',
                'success' => true,
            ]);
    }
    public function allRequests(SearchPurchaseOrderRequest $request)
    {
        $validated = $request->validated();
        $results = RequestPurchaseOrder::with([
            'requestCanvassSummary.priceQuotation.requestProcurement.requisitionSlip'
        ])
            ->when(
                $validated['po_number'] ?? false,
                fn ($q, $poNumber) =>
                $q->where('po_number', 'like', "%{$poNumber}%")
            )
            ->when(
                $validated['rs_number'] ?? false,
                fn ($q, $rsNumber) =>
                $q->whereHas(
                    'requestCanvassSummary.priceQuotation.requestProcurement.requisitionSlip',
                    fn ($subQuery) =>
                    $subQuery->where('reference_no', 'like', "%{$rsNumber}%")
                )
            )
            ->when(
                $validated['transaction_date'] ?? false,
                fn ($q, $date) =>
                $q->whereDate('transaction_date', $date)
            )
            ->orderByDesc('transaction_date')
            ->paginate(config('app.pagination.per_page', 15));
        return RequestPurchaseOrderListingResource::collection($results)->additional([
            'success' => true,
            'message' => 'Request Purchase Orders Successfully Fetched.',
        ]);
    }
}
