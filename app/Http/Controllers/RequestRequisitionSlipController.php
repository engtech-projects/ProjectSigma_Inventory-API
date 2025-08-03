<?php

namespace App\Http\Controllers;

use App\Enums\AssignTypes;
use App\Enums\RequestStatuses;
use App\Models\RequestRequisitionSlip;
use App\Http\Requests\StoreRequestRequisitionSlipRequest;
use App\Http\Resources\RequisitionSlipDetailedResource;
use App\Http\Resources\RequisitionSlipListingResource;
use App\Models\RequestStockItem;
use App\Models\SetupDepartments;
use App\Models\SetupProjects;
use App\Notifications\RequestStockForApprovalNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

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
            $attributes["section_type"] = AssignTypes::DEPARTMENT->value;
        } elseif ($attributes["section_type"] == AssignTypes::PROJECT->value) {
            $attributes["section_type"] = AssignTypes::PROJECT->value;
        }
        $attributes['request_status'] = RequestStatuses::PENDING;
        $attributes['created_by'] = auth()->user()->id;
        // Generate reference number with retry logic
        if ($attributes["section_type"] == AssignTypes::DEPARTMENT->value) {
            $this->generateDepartmentReferenceNumber($attributes, $sectionId);
        } else {
            $this->generateProjectReferenceNumber($attributes, $sectionId);
        }
        // Execute transaction with retry logic
        $maxRetries = 5;
        $attempt = 0;
        do {
            try {
                return DB::transaction(function () use ($attributes, $request) {
                    $duplicatedAttr = RequestRequisitionSlip::where('reference_no', $attributes['reference_no'])
                        ->first();
                    if ($duplicatedAttr) {
                        throw new \Exception('The reference number has already been taken.');
                    }
                    $requisitionSlip = RequestRequisitionSlip::create($attributes);
                    foreach ($attributes['items'] as $item) {
                        RequestStockItem::create([
                            'request_stock_id' => $requisitionSlip->id,
                            'quantity' => $item['quantity'],
                            'unit' => $item['unit'],
                            'item_id' => $item['item_id'],
                            'specification' => $item['specification'],
                            'preferred_brand' => $item['preferred_brand'],
                            'reason' => $item['reason'],
                        ]);
                    }
                    if ($requisitionSlip->getNextPendingApproval()) {
                        $requisitionSlip->notify(new RequestStockForApprovalNotification($request->bearerToken(), $requisitionSlip));
                    }
                    return new JsonResponse([
                        'success' => true,
                        'message' => 'Requisition Slip Successfully Submitted.',
                    ], JsonResponse::HTTP_OK);
                });
            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->errorInfo[1] == 1062) { // Duplicate entry error
                    $attempt++;
                    if ($attempt >= $maxRetries) {
                        return new JsonResponse([
                            'success' => false,
                            'message' => 'Unable to generate unique reference number after ' . $maxRetries . ' attempts',
                        ], JsonResponse::HTTP_CONFLICT);
                    }
                    // Regenerate reference number for department type
                    if ($attributes["section_type"] == AssignTypes::DEPARTMENT->value) {
                        $this->generateDepartmentReferenceNumber($attributes, $sectionId);
                    } elseif ($attributes["section_type"] == AssignTypes::PROJECT->value) {
                        $this->generateProjectReferenceNumber($attributes, $sectionId);
                    }
                    // Short delay before retry
                    usleep(100000); // 100ms
                } else {
                    throw $e; // Re-throw if it's not a duplicate key error
                }
            } catch (\Exception $e) {
                return new JsonResponse([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], JsonResponse::HTTP_CONFLICT);
            }
        } while ($attempt < $maxRetries);
    }

    private function generateDepartmentReferenceNumber(array &$attributes, int $sectionId): void
    {
        $departmentCode = strtoupper(implode('-', array_map('ucwords', explode(' ', SetupDepartments::findOrFail($sectionId)->department_name))));

        $baseRef = "RS{$departmentCode}";
        $increment = RequestRequisitionSlip::where('reference_no', 'regexp', "^{$baseRef}-[0-9]+$")->count() + 1;
        $attributes['reference_no'] = $baseRef . '-' . str_pad($increment, 7, '0', STR_PAD_LEFT);
    }

    private function generateProjectReferenceNumber(array &$attributes, int $sectionId): void
    {
        $projectCode = SetupProjects::findOrFail($sectionId)->project_code;
        $latest    = RequestRequisitionSlip::where('reference_no', 'regexp', "^RS{$projectCode}-[0-9]+$")
            ->orderBy('reference_no', 'desc')
            ->lockForUpdate()
            ->value('reference_no');
        $next      = $latest ? ((int)substr($latest, strlen("RS{$projectCode}-")) + 1) : 1;
        $attributes['reference_no'] = "RS{$projectCode}-" . str_pad($next, 7, '0', STR_PAD_LEFT);
    }

    /**
     * Display the specified resource.
     */
    public function show(RequestRequisitionSlip $resource)
    {
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

}
