<?php

namespace App\Http\Controllers;

use App\Enums\AssignTypes;
use App\Enums\RequestStatuses;
use App\Models\RequestRequisitionSlip;
use App\Http\Requests\StoreRequestRequisitionSlipRequest;
use App\Http\Resources\RequisitionSlipDetailedResource;
use App\Http\Resources\RequisitionSlipListingResource;
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
        DB::transaction(function () use (&$requisitionSlip, $mappedItems, $attributes) {
            $requisitionSlip->fill($attributes);
            if ($requisitionSlip->section_type == AssignTypes::DEPARTMENT->value) {
                $requisitionSlip->reference_no = $this->generateDepartmentReferenceNumber($requisitionSlip->section_id);
            } else {
                $requisitionSlip->reference_no = $this->generateProjectReferenceNumber($requisitionSlip->section_id);
            }
            $requisitionSlip->save();
            $requisitionSlip->items()->createMany($mappedItems->toArray());
        });
        if ($requisitionSlip->getNextPendingApproval()) {
            $requisitionSlip->notify(new RequestStockForApprovalNotification($request->bearerToken(), $requisitionSlip));
        }
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
