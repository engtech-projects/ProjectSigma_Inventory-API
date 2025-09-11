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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RequestPurchaseOrderController extends Controller
{
    protected $purchaseOrderService;
    public function __construct(PurchaseOrderService $purchaseOrderService)
    {
        $this->purchaseOrderService = $purchaseOrderService;
    }
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
        return (new RequestPurchaseOrderDetailedResource($resource))
            ->additional([
                'message' => 'Request Purchase Order retrieved successfully.',
                'success' => true,
            ]);
    }
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
    public function allRequests()
    {
        $myRequest = $this->purchaseOrderService->getAllRequest();

        if ($myRequest->isEmpty()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No data found.',
            ], JsonResponse::HTTP_OK);
        }
        return RequestPurchaseOrderListingResource::collection($myRequest)
        ->additional([
            "success" => true,
            "message" => "Request Purchase Orders Successfully Fetched.",
        ]);
    }
    public function filter(SearchPurchaseOrderRequest $request)
    {
        $validated = $request->validated();

        $rsNumber        = $validated['rs_number'] ?? null;
        $poNumber        = $validated['po_number'] ?? null;
        $transactionDate = $validated['transaction_date'] ?? null;

        $results = RequestPurchaseOrder::with(['requestCanvassSummary.priceQuotation.requestProcurement.requisitionSlip'])
            ->when($poNumber, function ($query, $poNumber) {
                $query->where('po_number', 'like', "%{$poNumber}%");
            })
            ->when($rsNumber, function ($query, $rsNumber) {
                $query->whereHas('requestCanvassSummary.priceQuotation.requestProcurement.requisitionSlip', function ($q) use ($rsNumber) {
                    $q->where('reference_no', 'like', "%{$rsNumber}%");
                });
            })
            ->when($transactionDate, function ($query, $transactionDate) {
                $query->whereDate('transaction_date', $transactionDate);
            })
            ->orderBy('transaction_date', 'desc')
            ->limit(15)
            ->get();

        return RequestPurchaseOrderListingResource::collection($results)
        ->additional([
            "success" => true,
            "message" => "Request Purchase Orders Successfully Fetched.",
        ]);
    }
}
