<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatuses;
use App\Http\Requests\StoreRequestTurnoverRequest;
use App\Http\Requests\UpdateRequestTurnoverRequest;
use App\Http\Resources\RequestTurnoverDetailedResource;
use App\Http\Resources\RequestTurnoverListingResource;
use App\Http\Resources\RequestTurnoverResource;
use App\Models\RequestTurnover;
use App\Models\SetupDepartments;
use App\Models\SetupEmployees;
use App\Models\SetupProjects;
use App\Models\SetupWarehouses;
use App\Models\WarehouseStocksSummary;
use App\Notifications\RequestTurnoverForApprovalNotification;
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
            'from',
            'to',
            'createdBy',
            'approvedBy',
            'items.item'
        ])->latest();
        if ($request->has('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }
        if ($request->has('warehouse_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('from_id', $request->warehouse_id)
                  ->where('from_type', SetupWarehouses::class)
                  ->orWhere(function ($q) use ($request) {
                      $q->where('to_id', $request->warehouse_id)
                        ->where('to_type', SetupWarehouses::class);
                  });
            });
        }
        $turnovers = $query->paginate(config('app.pagination.per_page', 10));
        return RequestTurnoverListingResource::collection($turnovers)
        ->additional([
            "success" => true,
            "message" => "Request Turnovers Successfully Fetched.",
        ]);
    }

    public function incoming(Request $request, int $ownerId)
    {
        $query = RequestTurnover::with([
            'from', 'to', 'createdBy', 'approvedBy', 'items.item'
        ])
        ->where('to_id', $ownerId)
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

    public function outgoing(Request $request, int $ownerId)
    {
        $query = RequestTurnover::with([
            'from', 'to', 'createdBy', 'approvedBy', 'items.item'
        ])
        ->where('from_id', $ownerId)
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

    // public function store(StoreRequestTurnoverRequest $request)
    // {
    //     $validated = $request->validated();

    //     $slip = DB::transaction(function () use ($validated) {
    //         $items = collect($validated['items']);

    //         $hasTransfer = false;
    //         $hasReturn   = false;

    //         $processedItems = $items->map(function ($item) use (&$hasTransfer, &$hasReturn) {
    //             $remarks = $item['remarks'] ?? '';
    //             if ($remarks === 'Request to Transfer') {
    //                 $hasTransfer = true;
    //                 $finalRemarks = 'Request to Transfer';
    //             } elseif ($remarks === 'Request to Return') {
    //                 $hasReturn = true;
    //                 $finalRemarks = 'Request to Return';
    //             } elseif ($remarks === 'Others') {
    //                 $finalRemarks = $item['remarks_other'];
    //             } else {
    //                 $finalRemarks = $remarks ?: null;
    //             }
    //             return [
    //                 'item_id'    => $item['item_id'],
    //                 'quantity'   => $item['quantity'],
    //                 'uom'        => $item['uom'],
    //                 'condition'  => $item['condition'] ?? null,
    //                 'remarks'    => $finalRemarks,
    //             ];
    //         });
    //         $metadataFlags = [];
    //         if ($hasTransfer) {
    //             $metadataFlags['request_transfer'] = true;
    //         }
    //         if ($hasReturn) {
    //             $metadataFlags['request_return'] = true;
    //         }
    //         $metadata = array_merge(
    //             $validated['metadata'] ?? [],
    //             $metadataFlags
    //         );
    //         $requestTurnover = RequestTurnover::create([
    //             'date'               => $validated['date'],
    //             'from_warehouse_id'  => $validated['from_warehouse_id'],
    //             'to_warehouse_id'    => $validated['to_warehouse_id'],
    //             'reference_no'       => $this->generateTurnoverReferenceNumber(),
    //             'metadata'           => $metadata,
    //             'created_by'         => auth()->user()->id,
    //             'approvals'          => $validated['approvals'],
    //             'request_status'     => RequestStatuses::PENDING,
    //         ]);
    //         $requestTurnover->items()->createMany($processedItems->toArray());

    //         return $requestTurnover;
    //     });
    //     $slip->notifyNextApprover(RequestTurnoverForApprovalNotification::class);
    //     return new JsonResponse([
    //         'success' => true,
    //         'message' => 'Request Turnover created successfully.',
    //         'data'    => new RequestTurnoverResource($slip)
    //     ], JsonResponse::HTTP_CREATED);
    // }
    public function store(StoreRequestTurnoverRequest $request)
    {
        $validated = $request->validated();

        $slip = DB::transaction(function () use ($validated) {
            $items = collect($validated['items']);

            $hasTransfer = false;
            $hasReturn   = false;

            $processedItems = $items->map(function ($item) use (&$hasTransfer, &$hasReturn) {
                $remarks = $item['remarks'] ?? '';
                if ($remarks === 'Request to Transfer') {
                    $hasTransfer = true;
                    $finalRemarks = 'Request to Transfer';
                } elseif ($remarks === 'Request to Return') {
                    $hasReturn = true;
                    $finalRemarks = 'Request to Return';
                } elseif ($remarks === 'Others') {
                    $finalRemarks = $item['remarks_other'] ?? null;
                } else {
                    $finalRemarks = $remarks ?: null;
                }
                return [
                    'item_id'    => $item['item_id'],
                    'quantity'   => $item['quantity'],
                    'uom'        => $item['uom'],
                    'condition'  => $item['condition'] ?? null,
                    'remarks'    => $finalRemarks,
                ];
            });

            $metadataFlags = [];
            if ($hasTransfer) $metadataFlags['request_transfer'] = true;
            if ($hasReturn)   $metadataFlags['request_return']   = true;

            $requestTurnover = RequestTurnover::create([
                'date'          => $validated['date'],
                'from_type'     => $this->resolveMorphType($validated['from_type']),
                'from_id'       => $validated['from_id'],
                'to_type'       => $this->resolveMorphType($validated['to_type']),
                'to_id'         => $validated['to_id'],
                'reference_no'  => $this->generateTurnoverReferenceNumber(),
                'metadata'      => array_merge($validated['metadata'] ?? [], $metadataFlags),
                'created_by'    => auth()->user()->id,
                'approvals'     => $validated['approvals'] ?? [],
                'request_status' => RequestStatuses::PENDING,
            ]);

            $requestTurnover->items()->createMany($processedItems->toArray());

            return $requestTurnover;
        });

        $slip->notifyNextApprover(RequestTurnoverForApprovalNotification::class);

        return new JsonResponse([
            'success' => true,
            'message' => 'Request Turnover created successfully.',
            'data'    => new RequestTurnoverResource($slip)
        ], JsonResponse::HTTP_CREATED);
    }
    private function resolveMorphType($type)
    {
        $morphMap = [
            'Warehouse' => SetupWarehouses::class,
            'Department' => SetupDepartments::class,
            'Project' => SetupProjects::class,
            'Employee' => SetupEmployees::class,

            SetupWarehouses::class => SetupWarehouses::class,
            SetupDepartments::class => SetupDepartments::class,
            SetupProjects::class => SetupProjects::class,
            SetupEmployees::class => SetupEmployees::class,
        ];

        return $morphMap[$type] ?? $type;
    }

    /**
     * Display the specified resource.
     */
    public function show(RequestTurnover $resource)
    {
        $resource->load([
            'from',
            'to',
            'createdBy',
            'approvedBy',
            'items.item'
        ]);

        return new JsonResponse([
            "success" => true,
            "message" => "Successfully fetched.",
            "data" => new RequestTurnoverDetailedResource($resource)
        ]);
    }
    public function transferRequests(Request $request)
    {
        $turnovers = RequestTurnover::transferRequests()
            ->with([
                'from',
                'to',
                'createdBy',
                'approvedBy',
                'items.item'
            ])
            ->latest()
            ->paginate($request->get('per_page', 15));

        return RequestTurnoverListingResource::collection($turnovers)->additional([
            "success" => true,
            "message" => "Transfer Requests Successfully Fetched.",
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
            'from',
            'to',
            'createdBy',
            'approvedBy',
            'items.item'
        ]));
    }
    public function allRequests()
    {
        $fetchData = RequestTurnover::latest()
        ->paginate(config('app.pagination.per_page', 10));
        return RequestTurnoverListingResource::collection($fetchData)
        ->additional([
            "success" => true,
            "message" => "Request Turnovers Successfully Fetched.",
        ]);
    }
    public function myApprovals()
    {
        $fetchData = RequestTurnover::latest()
        ->myApprovals()
        ->paginate(config('app.pagination.per_page', 10));
        return RequestTurnoverListingResource::collection($fetchData)
        ->additional([
            "success" => true,
            "message" => "Request Turnovers Successfully Fetched.",
        ]);
    }

    public function myApprovalsTransferRequests()
    {
        $fetchData = RequestTurnover::transferRequests()
        ->myApprovals()
        ->paginate(config('app.pagination.per_page', 10));
        return RequestTurnoverListingResource::collection($fetchData)
        ->additional([
            "success" => true,
            "message" => "Request Turnovers Successfully Fetched.",
        ]);
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
    public function getItemsByFilter(Request $request)
    {
        $query = WarehouseStocksSummary::query()
            ->with(['item', 'uom'])
            ->where('quantity', '>', 0);

        // Filter by department
        if ($request->filled('department_id')) {
            $department = SetupDepartments::findOrFail($request->department_id);

            $query->whereHas('warehouse', function ($q) use ($department) {
                $q->where('owner_type', SetupDepartments::class)
                ->where('owner_id', $department->id);
            });
        }

        // Filter by project
        if ($request->filled('project_id')) {
            $project = SetupProjects::findOrFail($request->project_id);

            $query->whereHas('warehouse', function ($q) use ($project) {
                $q->where('owner_type', SetupProjects::class)
                ->where('owner_id', $project->id);
            });
        }

        // Filter by warehouse_id (standalone or project/warehouse)
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Filter by employee (who is PSS for the warehouse)
        if ($request->filled('employee_id')) {
            $employeeId = $request->employee_id;

            $query->whereHas('warehouse.pssUsers', function ($q) use ($employeeId) {
                $q->where('users.id', $employeeId);
            });
        }

        $summaries = $query->get();

        $items = $summaries->map(function ($summary) {
            $item = $summary->item;
            return [
                'id'                => $summary->id,
                'item_id'           => $item->id,
                'item_description'  => $item->item_description,
                'current_quantity'  => $summary->quantity,
                'uom'               => $summary->uom->name,
                'uom_id'            => $summary->uom_id,
                'condition'         => $summary->condition ?? null,
                'remarks'           => $summary->remarks ?? null,
                'metadata'          => $summary->metadata ?? null,
                'warehouse_id'      => $summary->warehouse_id,
                'warehouse_name'    => $summary->warehouse->name,
                'owner_name'        => $summary->warehouse->owner_name,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Items fetched successfully',
            'items'   => $items,
            // 'filters' => $request->only(['department_id', 'project_id', 'warehouse_id', 'employee_id']),
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
