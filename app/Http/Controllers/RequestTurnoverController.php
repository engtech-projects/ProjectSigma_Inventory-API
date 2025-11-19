<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequestTurnoverRequest;
use App\Http\Requests\UpdateRequestTurnoverRequest;
use App\Http\Resources\RequestTurnoverDetailedResource;
use App\Http\Resources\RequestTurnoverListingResource;
use App\Http\Resources\RequestTurnoverResource;
use App\Models\RequestTurnover;
use App\Models\SetupWarehouses;
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
                'reference_no' => $this->generateTurnoverReferenceNumber(),
                'metadata' => $validated['metadata'],
            ]);
            foreach ($validated['items'] as $item) {
                $requestTurnover->items()->create([
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'uom' => $item['uom'],
                    'condition' => $item['condition'] ?? null,
                    'remarks' => $item['remarks'] ?? null
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
        ]);

        return new RequestTurnoverResource($requestTurnover->fresh([
            'fromWarehouse',
            'toWarehouse',
            'requestedBy',
            'approvedBy',
            'items.item'
        ]));
    }
    public function getItemsByWarehouse($warehouseId)
    {
        $warehouse = SetupWarehouses::with([
            'stockSummary' => function ($q) {
                $q->where('quantity', '>', 0);
            },
            'stockSummary.item',
            'stockSummary.uom'
        ])->findOrFail($warehouseId);

        $items = $warehouse->stockSummary->map(function ($summary) {
            $item = $summary->item;
            return [
                'id' => $summary->id,
                'item_id' => $item->id,
                'item_description' => $item->item_description,
                'current_quantity' => $summary->quantity,
                'uom' => $summary->uom->name,
                'uom_id' => $summary->uom_id,
                'condition' => $summary->condition,
                'remarks' => $summary->remarks,
                'metadata' => $summary->metadata,
            ];
        });

        return response()->json([
            'success' => true,
            'warehouse' => $warehouse->name,
            'items' => $items
        ]);
    }
    private function generateTurnoverReferenceNumber()
    {
        $baseRef = "TS-CW-IMS";
        $latestRs = RequestTurnover::orderByRaw('CAST(SUBSTRING_INDEX(reference_no, "-", -1) AS UNSIGNED) DESC')
            ->first();
        $lastRefNo = $latestRs ? (int) last(explode('-', $latestRs->reference_no)) : 0;
        return $baseRef . '-' . str_pad($lastRefNo + 1, 4, '0', STR_PAD_LEFT);
    }
}
