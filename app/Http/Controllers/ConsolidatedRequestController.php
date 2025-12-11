<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateConsolidatedRequest;
use App\Http\Requests\StoreGeneratedConsolidatedRequest;
use App\Http\Resources\ConsolidatedRequestDetailedResource;
use App\Http\Resources\ConsolidatedRequestItemsResource;
use App\Http\Resources\ConsolidatedRequestListingResource;
use App\Http\Resources\ConsolidatedRequestResource;
use App\Models\ConsolidatedRequest;
use App\Models\ConsolidatedRequestItems;
use App\Models\RequestProcurement;
use App\Models\RequestRequisitionSlip;
use App\Models\RequestRequisitionSlipItems;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\JsonResponse;

class ConsolidatedRequestController extends Controller
{
    public function index()
    {
        $main = ConsolidatedRequest::with('items.requisitionSlipItem', 'items.requisitionSlip')
        ->latest()
        ->paginate(config('app.pagination.per_page', 10));
        return ConsolidatedRequestListingResource::collection($main)
        ->additional([
            "success" => true,
            "message" => "Consolidated Request Successfully Fetched.",
        ]);
    }
    public function unserved()
    {
        $query = RequestProcurement::query()
            ->isUnserved()
            ->isConsolidated()
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
        $flat = $requisitionSlips->flatMap(function ($rs) {
            return $rs->items->map(function ($item) use ($rs) {
                return [
                    'item_id'           => $item->item_id,
                    'specification'     => $item->specification,
                    'preferred_brand'   => $item->preferred_brand,
                    'uom_name'          => $item->uom_name,
                    'uom_symbol'        => $item->uom_symbol,
                    'item_description'  => $item->itemProfile?->item_description,
                    'rs_id'             => $rs->id,
                    'rs_reference_no'   => $rs->reference_no,
                    'quantity'          => $item->quantity,
                    'rs_item_id'        => $item->id,
                    'warehouse_name'    => $rs->warehouse?->warehouse_name,
                    'remarks'           => $rs->remarks,
                ];
            });
        });
        $grouped = $flat
            ->groupBy(function ($item) {
                return $item['item_id'] . '|' . $item['uom_name'];
            })
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'item_id'         => $first['item_id'],
                    'item_description' => $first['item_description'],
                    'specification'    => $group
                        ->pluck('specification')
                        ->filter()
                        ->unique()
                        ->implode('/'),
                    'preferred_brand'  => $group
                        ->pluck('preferred_brand')
                        ->filter()
                        ->unique()
                        ->implode('/'),
                    'uom_name'         => $first['uom_name'],
                    'uom_symbol'       => $first['uom_symbol'],
                    'total_quantity'   => $group->sum('quantity'),
                    'no_of_project_departments_requested' =>
                        $group->pluck('rs_id')->unique()->count(),
                    'rs_item_ids'      => $group->map(function ($item) {
                        return [
                            'rs_id'     => $item['rs_id'],
                            'rs_item_id'=> $item['rs_item_id'],
                            'quantity'  => $item['quantity'],
                        ];
                    })->values(),
                    'source_rs_refs' => $group->pluck('rs_reference_no')->unique()->values(),
                    'remarks'        => $first['remarks'],
                ];
            })
            ->values();
        return response()->json([
            'success' => true,
            'message' => 'Draft consolidated request generated successfully.',
            'data' => [
                'rs_ids' => $requisitionSlips->pluck('id'),
                'items' => $grouped,
            ],
        ]);
    }
    // public function store(StoreGeneratedConsolidatedRequest $request): JsonResponse
    // {
    //     return DB::transaction(function () use ($request) {
    //         $consolidated = ConsolidatedRequest::create([
    //             'reference_no'       => $this->generateReferenceNo(),
    //             'purpose'            => $request->purpose,
    //             'consolidated_by'    => auth()->user()->id,
    //             'date_consolidated'  => now(),
    //             'status'             => 'draft',
    //             'metadata'           => [
    //                 'selected_rs' => $request->rs_ids,
    //             ],
    //         ]);
    //         $itemsToInsert = [];
    //         foreach ($request->items as $item) {
    //             foreach ($item['rs_item_ids'] as $source) {
    //                 $itemsToInsert[] = [
    //                     'consolidated_request_id'   => $consolidated->id,
    //                     'requisition_slip_id'       => $source['rs_id'],
    //                     'requisition_slip_item_id'  => $source['rs_item_id'],
    //                     'quantity_consolidated'     => $source['quantity'],
    //                     'status'                    => 'Pending',
    //                     'remarks'                   => $item['remarks'] ?? null,
    //                     'created_at'                => now(),
    //                     'updated_at'                => now(),
    //                 ];
    //             }
    //         }
    //         if (empty($itemsToInsert)) {
    //             throw new \Exception("No items to consolidate.");
    //         }
    //         ConsolidatedRequestItems::insert($itemsToInsert);
    //         return new JsonResponse([
    //             'success' => true,
    //             'message' => 'Consolidated request created successfully.',
    //             'data' => new ConsolidatedRequestResource(
    //                 $consolidated->load([
    //                     'items.requisitionSlip',
    //                     'items.requisitionSlipItem',
    //                 ])
    //             ),
    //         ], JsonResponse::HTTP_OK);
    //     });
    // }
    public function store(StoreGeneratedConsolidatedRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $consolidated = ConsolidatedRequest::create([
                'reference_no'       => $this->generateReferenceNo(),
                'purpose'            => $request->purpose,
                'consolidated_by'    => auth()->user()->id,
                'date_consolidated'  => now(),
                'status'             => 'draft',
                'metadata'           => [
                    'selected_rs' => $request->rs_ids,
                ],
            ]);
            $itemsToInsert = [];
            $rsItemIdsToUpdate = [];
            foreach ($request->items as $item) {
                foreach ($item['rs_item_ids'] as $source) {

                    $itemsToInsert[] = [
                        'consolidated_request_id'   => $consolidated->id,
                        'requisition_slip_id'       => $source['rs_id'],
                        'requisition_slip_item_id'  => $source['rs_item_id'],
                        'quantity_consolidated'     => $source['quantity'],
                        'status'                    => 'pending',
                        'remarks'                   => $item['remarks'] ?? null,
                        'created_at'                => now(),
                        'updated_at'                => now(),
                    ];
                    $rsItemIdsToUpdate[] = $source['rs_item_id'];
                }
            }
            ConsolidatedRequestItems::insert($itemsToInsert);
            RequestRequisitionSlipItems::whereIn('id', $rsItemIdsToUpdate)
                ->update(['consolidated_request_id' => $consolidated->id]);
            return new JsonResponse([
                'success' => true,
                'message' => 'Consolidated request created successfully.',
                'data' => new ConsolidatedRequestResource(
                    $consolidated->load([
                        'items.requisitionSlip',
                        'items.requisitionSlipItem',
                    ])
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
