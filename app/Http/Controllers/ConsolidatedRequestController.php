<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateConsolidatedRequest;
use App\Http\Requests\StoreGeneratedConsolidatedRequest;
use App\Http\Resources\ConsolidatedRequestDetailedResource;
use App\Http\Resources\ConsolidatedRequestItemsResource;
use App\Http\Resources\ConsolidatedRequestResource;
use App\Models\ConsolidatedRequest;
use App\Models\ConsolidatedRequestItems;
use App\Models\RequestProcurement;
use App\Models\RequestRequisitionSlip;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\JsonResponse;

class ConsolidatedRequestController extends Controller
{
    public function index()
    {
        $main = ConsolidatedRequest::with('items.requisitionSlipItem', 'items.requisitionSlip')
        ->latest()
        ->paginate(config('app.pagination.per_page', 10));
        return ConsolidatedRequestResource::collection($main)
        ->additional([
            "success" => true,
            "message" => "Consolidated Request Successfully Fetched.",
        ]);
    }
    public function unserved()
    {
        $query = RequestProcurement::query()
            ->isUnserved()
            ->with('requisitionSlip', 'requisitionSlipItems', 'requisitionSlipItems.itemProfile', 'requisitionSlipItems.uom')
            ->latest()
            ->paginate(config('app.pagination.per_page', 10));
        return ConsolidatedRequestItemsResource::collection($query)
            ->additional([
                'success' => true,
                'message' => 'Unserved request procurements fetched successfully.',
            ]);
    }
    public function generateDraft(GenerateConsolidatedRequest $request): JsonResponse
    {
        $rsIds = $request->input('rs_ids', []);
        $requisitionSlips = RequestRequisitionSlip::query()
            ->whereIn('id', $rsIds)
            ->with([
                'items.itemProfile',
                'items.uom',
                'warehouse',
            ])
            ->get();
        if ($requisitionSlips->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No valid requisition slips found.',
            ], 404);
        }
        $draftItems = $requisitionSlips
            ->flatMap(fn ($rs) => $rs->items->map(function ($item) use ($rs) {
                return [
                    'rs_id' => $rs->id,
                    'rs_reference_no' => $rs->reference_no,
                    'item_id' => $item->item_id,
                    'item_description' => $item->itemProfile?->item_description,
                    'specification' => $item->specification,
                    'preferred_brand' => $item->preferred_brand,
                    'quantity' => $item->quantity,
                    'uom_name' => $item->uom_name ?? $item->uom?->uom_name,
                    'warehouse_name' => $rs->warehouse?->warehouse_name,
                    'remarks' => $rs->remarks,
                ];
            }))
            ->values();
        return response()->json([
            'success' => true,
            'message' => 'Draft consolidated request generated successfully.',
            'data' => [
                'rs_ids' => $requisitionSlips->pluck('id'),
                'items' => $draftItems,
            ],
        ]);
    }
    public function store(StoreGeneratedConsolidatedRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $consolidated = ConsolidatedRequest::create([
                'reference_no' => $this->generateReferenceNo(),
                'purpose' => $request->purpose,
                'consolidated_by' => auth()->user()->id,
                'date_consolidated' => now(),
                'status' => 'draft',
                'remarks' => $request->remarks,
                'metadata' => [
                    'selected_rs' => $request->request_requisition_slip_ids,
                ],
            ]);
            $itemsToInsert = [];
            $items = $request->items;
            foreach ($items as $consolidatedItem) {
                $rsItemData = $consolidatedItem['rs_item_ids'];
                $quantity = array_column($rsItemData, 'quantity');
                $rsIds = array_column($rsItemData, 'rs_id');
                $rsItemIds = array_column($rsItemData, 'rs_item_id');
                $itemsToInsert = array_merge($itemsToInsert, array_map(
                    fn ($qty, $rsId, $rsItemId) => [
                        'consolidated_request_id' => $consolidated->id,
                        'requisition_slip_id' => $rsId,
                        'requisition_slip_item_id' => $rsItemId,
                        'quantity_consolidated' => $qty,
                        'status' => 'pending',
                        'remarks' => $request->remarks,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    $quantity,
                    $rsIds,
                    $rsItemIds
                ));
            }
            if (empty($itemsToInsert)) {
                throw new \Exception('No items to consolidate.');
            }
            ConsolidatedRequestItems::insert($itemsToInsert);
            return new JsonResponse([
                'success' => true,
                'message' => 'Consolidated request created successfully.',
                'data' => new ConsolidatedRequestResource(
                    $consolidated->load('items.requisitionSlipItem', 'items.requisitionSlip')
                ),
            ], JsonResponse::HTTP_OK);
        });
    }
    private function generateReferenceNo(): string
    {
        $baseRef = "CR-SP-IMS";
        $latest = ConsolidatedRequest::orderByRaw('CAST(SUBSTRING_INDEX(reference_no, "-", -1) AS UNSIGNED) DESC')
            ->first();
        $lastNumber = $latest ? (int) last(explode('-', $latest->reference_no)) : 0;
        $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        return "{$baseRef}-{$nextNumber}";
    }

    public function show(ConsolidatedRequest $resource)
    {
        $resource->load(['items.requisitionSlipItem', 'items.requisitionSlip']);

        return response()->json([
            "success" => true,
            "message" => "Successfully fetched.",
            "data" => new ConsolidatedRequestDetailedResource($resource),
        ]);
    }
}
