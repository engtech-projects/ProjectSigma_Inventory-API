<?php

namespace App\Http\Controllers;

use App\Http\Resources\ConsolidatedRequestItemsResource;
use App\Http\Resources\ConsolidatedRequestResource;
use App\Models\ConsolidatedRequest;
use App\Models\RequestProcurement;
use App\Models\RequestRequisitionSlip;
use Illuminate\Http\Request;
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
            ->with('requisitionSlip', 'requisitionSlipItems.itemProfile', 'requisitionSlipItems.uom')
            ->latest()
            ->paginate(config('app.pagination.per_page', 10));
        return ConsolidatedRequestItemsResource::collection($query)
            ->additional([
                'success' => true,
                'message' => 'Unserved request procurements fetched successfully.',
            ]);
    }
    public function generateDraft(Request $request): JsonResponse
{
    $request->validate([
        'request_requisition_slip_ids' => 'required|array|min:1',
        'request_requisition_slip_ids.*' => 'exists:request_requisition_slips,id',
    ]);

    // Fetch all selected RS with their items and related models
    $requisitionSlips = RequestRequisitionSlip::query()
        ->whereIn('id', $request->request_requisition_slip_ids)
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

    // Combine all items from the selected RS
    $draftItems = $requisitionSlips
        ->flatMap(fn($rs) => $rs->items->map(function ($item) use ($rs) {
            return [
                'requisition_slip_id' => $rs->id,
                'requisition_slip_reference_no' => $rs->reference_no,
                'item_id' => $item->item_id,
                'item_description' => $item->itemProfile?->item_description,
                'specification' => $item->specification,
                'preferred_brand' => $item->preferred_brand,
                'quantity' => $item->quantity,
                'uom_name' => $item->uom_name,
                'warehouse_name' => $rs->warehouse?->warehouse_name,
                'remarks' => $rs->remarks,
            ];
        }))
        ->values();

    return response()->json([
        'success' => true,
        'message' => 'Draft consolidated request generated successfully.',
        'data' => [
            'selected_rs' => $requisitionSlips->pluck('reference_no'),
            'total_items' => $draftItems->count(),
            'items' => $draftItems,
        ],
    ]);
}


    public function store(Request $request)
    {

    }
    public function show(ConsolidatedRequest $consolidatedRequest)
    {
        return "show";
    }
}
