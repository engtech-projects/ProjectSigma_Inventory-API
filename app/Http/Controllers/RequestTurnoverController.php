<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequestTurnoverRequest;
use App\Http\Requests\UpdateRequestTurnoverRequest;
use App\Http\Resources\RequestTurnoverDetailedResource;
use App\Http\Resources\RequestTurnoverListingResource;
use App\Http\Resources\RequestTurnoverResource;
use App\Models\RequestTurnover;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequestTurnoverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = RequestTurnover::with([
            'fromWarehouse',
            'toWarehouse',
            'requestedBy',
            'approvedBy',
            'items.item'
        ])->latest();
        if ($request->has('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }
        if ($request->has('warehouse_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('from_warehouse_id', $request->warehouse_id)
                  ->orWhere('to_warehouse_id', $request->warehouse_id);
            });
        }
        $turnovers = $query->paginate(config('app.pagination.per_page', 10));
        return RequestTurnoverListingResource::collection($turnovers)
        ->additional([
            "success" => true,
            "message" => "Request Turnovers Successfully Fetched.",
        ]);
    }

    public function incoming(Request $request, int $warehouse)
    {
        $query = RequestTurnover::with([
            'fromWarehouse',
            'toWarehouse',
            'requestedBy',
            'approvedBy',
            'items.item'
        ])
        ->where('to_warehouse_id', $warehouse)
        ->latest();
        if ($request->has('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }

        $turnovers = $query->paginate($request->get('per_page', 15));

        return RequestTurnoverDetailedResource::collection($turnovers)
        ->additional([
            "success" => true,
            "message" => "Incoming Request Turnovers Successfully Fetched.",
        ]);
    }
    public function outgoing(Request $request, int $warehouse)
    {
        $query = RequestTurnover::with([
            'fromWarehouse',
            'toWarehouse',
            'requestedBy',
            'approvedBy',
            'items.item'
        ])
        ->where('from_warehouse_id', $warehouse)
        ->latest();
        if ($request->has('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }
        $turnovers = $query->paginate($request->get('per_page', 15));
        return RequestTurnoverDetailedResource::collection($turnovers)
        ->additional([
            "success" => true,
            "message" => "Outgoing Request Turnovers Successfully Fetched.",
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequestTurnoverRequest $request)
    {
        $validated = $request->validated();
        $request = DB::transaction(function () use ($validated) {
            $requestTurnover = RequestTurnover::create([
                'date' => $validated['date'],
                'from_warehouse_id' => $validated['from_warehouse_id'],
                'to_warehouse_id' => $validated['to_warehouse_id'],
                'requested_by' => auth()->user()->id,
                'remarks' => $validated['remarks'],
                'metadata' => $validated['metadata'],
            ]);
            foreach ($validated['items'] as $item) {
                $requestTurnover->items()->create([
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'uom' => $item['uom'],
                    'condition' => $item['condition'] ?? null,
                    'remarks' => $item['remarks'] ?? null,
                ]);
            }
            return $requestTurnover;
        });
        return new JsonResponse([
            'success' => true,
            'message' => 'Request Turnover created successfully.',
            'data' => new RequestTurnoverResource($request),
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(RequestTurnover $resource)
    {
        $resource->load([
            'fromWarehouse',
            'toWarehouse',
            'requestedBy',
            'approvedBy',
            'items.item'
        ]);

        return new JsonResponse([
            "success" => true,
            "message" => "Successfully fetched.",
            "data" => new RequestTurnoverDetailedResource($resource)
        ]);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequestTurnoverRequest $request, RequestTurnover $requestTurnover)
    {
        if (!$requestTurnover->canBeUpdated()) {
            return response()->json(['message' => 'Cannot update this request turnover'], 422);
        }

        $requestTurnover->update([
            'received_date' => $request->received_date,
            'received_name' => $request->received_name,
            'remarks' => $request->remarks ?? $requestTurnover->remarks,
        ]);

        return new RequestTurnoverResource($requestTurnover->fresh([
            'fromWarehouse',
            'toWarehouse',
            'requestedBy',
            'approvedBy',
            'items.item'
        ]));
    }

    // public function acceptItem(AcceptItemRequest $request, RequestTurnoverItem $item)
    // {
    //     if (!$item->canBeAccepted()) {
    //         return response()->json(['message' => 'Cannot accept this item'], 422);
    //     }

    //     DB::beginTransaction();

    //     try {
    //         // Update item status
    //         $item->update([
    //             'accept_status' => 'accepted',
    //             'remarks' => $request->remarks ?? $item->remarks,
    //         ]);

    //         $turnover = $item->requestTurnover;

    //         // Create stock out transaction for from_warehouse
    //         $this->createStockTransaction(
    //             $turnover->from_warehouse_id,
    //             $item->item_id,
    //             -abs($item->quantity),
    //             'stock_out',
    //             "Request Turnover: {$turnover->reference_no}",
    //             $item->id
    //         );

    //         // Create stock in transaction for to_warehouse (Material Receipt)
    //         $this->createStockTransaction(
    //             $turnover->to_warehouse_id,
    //             $item->item_id,
    //             abs($item->quantity),
    //             'stock_in',
    //             "Material Receipt from {$turnover->fromWarehouse->name} - RT: {$turnover->reference_no}",
    //             $item->id
    //         );

    //         // Check if all items are processed and update turnover status
    //         if ($turnover->getPendingItemsCount() === 0) {
    //             $turnover->update([
    //                 'approval_status' => 'approved',
    //                 'approved_by' => Auth::id(),
    //             ]);
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'message' => 'Item accepted successfully',
    //             'item' => $item->fresh('item'),
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['message' => 'Failed to accept item', 'error' => $e->getMessage()], 500);
    //     }
    // }

    // /**
    //  * Deny a request turnover item
    //  */
    // public function denyItem(DenyItemRequest $request, RequestTurnoverItem $item)
    // {
    //     if (!$item->canBeDenied()) {
    //         return response()->json(['message' => 'Cannot deny this item'], 422);
    //     }

    //     $item->update([
    //         'accept_status' => 'denied',
    //         'remarks' => $request->remarks ?? $item->remarks,
    //     ]);

    //     // Check if all items are processed and update turnover status
    //     $turnover = $item->requestTurnover;
    //     if ($turnover->getPendingItemsCount() === 0) {
    //         $hasAccepted = $turnover->getAcceptedItemsCount() > 0;
    //         $turnover->update([
    //             'approval_status' => $hasAccepted ? 'approved' : 'rejected',
    //             'approved_by' => Auth::id(),
    //         ]);
    //     }

    //     return response()->json([
    //         'message' => 'Item denied successfully',
    //         'item' => $item->fresh('item'),
    //     ]);
    // }
    // private function createStockTransaction(
    //     int $warehouseId,
    //     int $itemId,
    //     float $quantity,
    //     string $type,
    //     string $reference,
    //     int $relatedId
    // ) {
    //     // Assuming you have a StockTransaction model
    //     \App\Models\StockTransaction::create([
    //         'warehouse_id' => $warehouseId,
    //         'item_id' => $itemId,
    //         'quantity' => $quantity,
    //         'type' => $type,
    //         'reference' => $reference,
    //         'reference_type' => 'request_turnover_item',
    //         'reference_id' => $relatedId,
    //         'transaction_date' => now(),
    //         'created_by' => Auth::id(),
    //     ]);
    // }
}
