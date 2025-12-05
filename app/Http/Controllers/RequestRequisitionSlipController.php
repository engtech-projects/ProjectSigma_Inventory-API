<?php

namespace App\Http\Controllers;

use App\Enums\AssignTypes;
use App\Enums\RequestStatuses;
use App\Models\RequestRequisitionSlip;
use App\Http\Requests\StoreRequestRequisitionSlipRequest;
use App\Http\Resources\RequisitionSlipDetailedResource;
use App\Http\Resources\RequisitionSlipListingResource;
use App\Models\RequestTurnover;
use App\Models\SetupDepartments;
use App\Models\SetupProjects;
use App\Models\SetupWarehouses;
use App\Notifications\RequestStockForApprovalNotification;
use App\Notifications\RequestTurnoverForApprovalNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class RequestRequisitionSlipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $main = RequestRequisitionSlip::latest()
        ->paginate(config('app.pagination.per_page', 10));
        return RequisitionSlipListingResource::collection($main)
        ->additional([
            "success" => true,
            "message" => "Request Requisition Slips Successfully Fetched.",
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequestRequisitionSlipRequest $request)
    {
        $attributes = $request->validated();
        $sectionId = $attributes['section_id'];
        if ($attributes['type_of_request'] === 'Consolidated Request for the month of' && !empty($attributes['month'])) {
            $attributes['type_of_request'] = $attributes['type_of_request'] . ' ' . $attributes['month'];
            unset($attributes['month']);
        }
        if ($attributes["section_type"] == AssignTypes::DEPARTMENT->value) {
            $attributes["warehouse_id"] = 1; // DEFAULT WAREHOUSE FOR ALL DEPARTMENTS
        } elseif ($attributes["section_type"] == AssignTypes::PROJECT->value) {
            $attributes["warehouse_id"] = SetupProjects::find($attributes["section_id"])->warehouse->id;
        }
        $attributes['request_status'] = RequestStatuses::PENDING;
        $attributes['created_by'] = auth()->user()->id;
        $mappedItems = collect($attributes['items'])->map(function ($item) {
            return [
                'quantity' => $item['quantity'],
                'unit' => $item['unit'],
                'item_id' => $item['item_id'],
                'specification' => $item['specification'],
                'preferred_brand' => $item['preferred_brand'],
                'reason' => $item['reason'],
            ];
        });
        unset($attributes['items']);
        $requisitionSlip = new RequestRequisitionSlip();
        $requisitionSlip = DB::transaction(function () use (&$requisitionSlip, $mappedItems, $attributes) {
            $requisitionSlip->fill($attributes);
            if ($requisitionSlip->section_type == AssignTypes::DEPARTMENT->value) {
                $requisitionSlip->reference_no = $this->generateDepartmentReferenceNumber($requisitionSlip->section_id);
            } else {
                $requisitionSlip->reference_no = $this->generateProjectReferenceNumber($requisitionSlip->section_id);
            }
            $requisitionSlip->save();
            $requisitionSlip->items()->createMany($mappedItems->toArray());
            return $requisitionSlip->refresh();
        });
        $requisitionSlip->notifyNextApprover(RequestStockForApprovalNotification::class);
        return new JsonResponse([
            'success' => true,
            'message' => 'Requisition Slip Successfully Submitted.',
        ], JsonResponse::HTTP_OK);
    }

    private function generateDepartmentReferenceNumber($departmentId)
    {
        $departmentCode = SetupDepartments::findOrFail($departmentId)->code;
        $baseRef = "RS{$departmentCode}";
        $latestRs = RequestRequisitionSlip::orderByRaw('SUBSTRING_INDEX(reference_no, \'-\', -1) DESC')
            ->first();
        $lastRefNo = $latestRs ? collect(explode('-', $latestRs->reference_no))->last() : 0;
        return $baseRef . '-' . str_pad($lastRefNo + 1, 7, '0', STR_PAD_LEFT);
    }

    private function generateProjectReferenceNumber($projectId)
    {
        $projectCode = SetupProjects::findOrFail($projectId)->project_code;
        $baseRef = "RS{$projectCode}";
        $latestRs = RequestRequisitionSlip::orderByRaw('SUBSTRING_INDEX(reference_no, \'-\', -1) DESC')
            ->first();
        $lastRefNo = $latestRs ? collect(explode('-', $latestRs->reference_no))->last() : 0;
        return $baseRef . '-' . str_pad($lastRefNo + 1, 7, '0', STR_PAD_LEFT);
    }

    /**
     * Display the specified resource.
     */
    public function show(RequestRequisitionSlip $resource)
    {
        $resource->loadMissing([
            'items' => function ($query) {
                $query->with(['warehouseStocks' => function ($q) {
                    $q->where('quantity', '>', 0)
                      ->with(['warehouse:id,name,location', 'uom:id,name'])
                      ->select('id', 'item_id', 'warehouse_id', 'quantity', 'uom_id');
                }]);
            }
        ]);
        return response()->json([
            "message" => "Successfully fetched.",
            "success" => true,
            "data" => new RequisitionSlipDetailedResource($resource)
        ]);
    }

    public function myRequests()
    {
        $fetchData = RequestRequisitionSlip::latest()
        ->myRequests()
        ->paginate(config('app.pagination.per_page', 10));
        return RequisitionSlipListingResource::collection($fetchData)
        ->additional([
            "success" => true,
            "message" => "Request Requisition Slips Successfully Fetched.",
        ]);
    }

    public function allRequests()
    {
        $fetchData = RequestRequisitionSlip::latest()
        ->paginate(config('app.pagination.per_page', 10));
        return RequisitionSlipListingResource::collection($fetchData)
        ->additional([
            "success" => true,
            "message" => "Request Requisition Slips Successfully Fetched.",
        ]);
    }

    public function myApprovals()
    {
        $fetchData = RequestRequisitionSlip::latest()
        ->myApprovals()
        ->paginate(config('app.pagination.per_page', 10));
        return RequisitionSlipListingResource::collection($fetchData)
        ->additional([
            "success" => true,
            "message" => "Request Requisition Slips Successfully Fetched.",
        ]);
    }
    public function allocateStock(
        Request $request,
        RequestRequisitionSlip $requisitionSlip,
        $itemId
    ): JsonResponse {
        $request->validate([
            'allocations' => 'required|array|min:1',
            'allocations.*.warehouse_id' => 'required|integer|exists:setup_warehouses,id',
            'allocations.*.quantity' => 'required|integer|min:1',
            'allocations.*.uom_id' => 'required|integer|exists:setup_uom,id',
        ]);

        $rsItem = $requisitionSlip->items()->findOrFail($itemId);
        $toWarehouseId = $requisitionSlip->warehouse_id; // Destination

        $totalAllocated = collect($request->allocations)->sum('quantity');

        $metadata = [
            'item_details' => [
                'quantity_requested' => $rsItem->quantity,
                'uom' => $rsItem->uom_name,
                'item_description' => $rsItem->item_description,
                'specification' => $rsItem->specification,
                'preferred_brand' => $rsItem->preferred_brand,
                'reason' => $rsItem->reason,
            ],
            'suggested_allocation' => $request->allocations,
            'allocated_by' => auth()->user()->id,
            'allocated_at' => now()->format('M d, Y h:i A'),
            'total_allocated' => $totalAllocated,
            'requisition_slip_id' => $requisitionSlip->id,
            'requisition_slip_reference' => $requisitionSlip->reference_no,
        ];

        DB::transaction(function () use (
            $rsItem,
            $request,
            $toWarehouseId,
            $requisitionSlip,
            $metadata
        ) {
            // 1. Save allocation suggestion in RS item
            $rsItem->update([
                'metadata' => $metadata,
                'location' => collect($request->allocations)->pluck('warehouse')->implode(', '),
                'location_qty' => collect($request->allocations)->sum('quantity'),
            ]);

            // 2. Create ONE RequestTurnover per source warehouse
            foreach ($request->allocations as $alloc) {
                $fromWarehouseId = $alloc['warehouse_id'];
                $qty = $alloc['quantity'];
                $uom = $alloc['uom'];

                if ($qty <= 0) {
                    continue;
                }

                // Prevent duplicate turnover for same RS + item + from/to warehouse
                $existingTurnover = RequestTurnover::where('from_warehouse_id', $fromWarehouseId)
                    ->where('to_warehouse_id', $toWarehouseId)
                    ->whereJsonContains('metadata->requisition_slip_id', $requisitionSlip->id)
                    ->whereHas('items', fn ($q) => $q->where('item_id', $rsItem->item_id))
                    ->first();

                if ($existingTurnover) {
                    // Just increase quantity in existing item
                    $existingTurnover->items()
                        ->where('item_id', $rsItem->item_id)
                        ->increment('quantity', $qty);
                    continue;
                }
                $fromWarehouse = SetupWarehouses::with('managers')->find($fromWarehouseId);

                if ($fromWarehouse->managers->isEmpty()) {
                    throw new \Exception("No PSS Manager assigned to warehouse: {$fromWarehouse->name}");
                }

                $approvals = $fromWarehouse->managers->map(function ($manager) {
                    return [
                        'status'      => 'Pending',
                        'user_id'     => $manager->id,
                        'remarks'     => null,
                    ];
                })->toArray();
                // Create new RequestTurnover (follows your store() logic exactly)
                $turnover = RequestTurnover::create([
                    'date' => now()->toDateString(),
                    'from_warehouse_id' => $fromWarehouseId,
                    'to_warehouse_id' => $toWarehouseId,
                    'created_by' => auth()->user()->id,
                    'request_status' => RequestStatuses::PENDING,
                    'metadata' => [
                        'source' => 'auto_generated_from_rs_allocation',
                        'requisition_slip_id' => $requisitionSlip->id,
                        'requisition_slip_reference' => $requisitionSlip->reference_no,
                        'allocated_by' => auth()->user()->name,
                        'allocated_at' => now()->toDateTimeString(),
                    ],
                    'approvals' => $approvals,
                ]);

                // Create item in pivot table — correct place!
                $turnover->items()->create([
                    'item_id' => $rsItem->item_id,
                    'quantity' => $qty,
                    'uom' => $alloc['uom_id'],
                    'condition' => 'Good',
                    'remarks' => "Request to Transfer",
                    'accept_status' => 'Pending',
                ]);

                // Send approval notification to FROM warehouse
                $turnover->notifyNextApprover(RequestTurnoverForApprovalNotification::class);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Stock allocation saved and turnover requests created successfully!',
            'data' => $metadata
        ]);
    }
}
