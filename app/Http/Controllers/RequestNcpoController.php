<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNcpoRequest;
use App\Http\Resources\RequestNcpoDetailedResource;
use App\Http\Resources\RequestNcpoListingResource;
use App\Http\Resources\RequestNcpoResource;
use App\Models\RequestNcpo;
use App\Notifications\RequestNcpoForApprovalNotification;
use Illuminate\Support\Facades\DB;

class RequestNcpoController extends Controller
{
    public function index()
    {
        $requestNCPOs = RequestNcpo::paginate(config('app.pagination.per_page', 15));
        return RequestNcpoListingResource::collection($requestNCPOs)
        ->additional([
            'message' => 'Request NCPOs retrieved successfully.',
            'success' => true,
        ]);
    }

    public function store(StoreNcpoRequest $request)
    {
        $validated = $request->validated();
        $ncpo = DB::transaction(function () use ($validated) {
            $ncpoNo = RequestNcpo::generateReferenceNumber(
                'ncpo_no',
                fn ($prefix, $datePart, $number) => "{$prefix}-{$datePart}-{$number}",
                ['prefix' => 'NCPO', 'dateFormat' => 'Y-m']
            );
            $ncpo = RequestNcpo::create([
                'ncpo_no' => $ncpoNo,
                'date' => $validated['date'],
                'justification' => $validated['justification'],
                'po_id' => $validated['po_id'],
                'created_by' => auth()->user()->id,
                'approvals' => $validated['approvals'],
            ]);
            $ncpo->items()->createMany(
                collect($validated['items'])->map(fn ($item) => [
                    ...$item,
                    'request_ncpo_id' => $ncpo->id,
                ])->toArray()
            );
            return $ncpo->load('items');
        });
        $ncpo->notifyNextApprover(RequestNcpoForApprovalNotification::class);
        return RequestNcpoResource::make($ncpo)->additional([
            'message' => 'Request NCPO created successfully.',
            'success' => true,
        ]);
    }
    public function show(RequestNcpo $resource)
    {
        $resource->load([
            'items.item',
            'purchaseOrder',
        ]);
        return RequestNcpoDetailedResource::make($resource)
            ->additional([
                'message' => 'Request NCPO retrieved successfully.',
                'success' => true,
            ]);
    }
    public function myRequests()
    {
        $fetchData = RequestNcpo::latest()
        ->myRequests()
        ->paginate(config('app.pagination.per_page', 10));
        return RequestNcpoListingResource::collection($fetchData)
        ->additional([
            "success" => true,
            "message" => "Request Requisition Slips Successfully Fetched.",
        ]);
    }
    public function allRequests()
    {
        $fetchData = RequestNcpo::with('purchaseOrder')
            ->latest()
        ->paginate(config('app.pagination.per_page', 10));
        return RequestNcpoListingResource::collection($fetchData)
        ->additional([
            "success" => true,
            "message" => "Request NCPOs Successfully Fetched.",
        ]);
    }
    public function myApprovals()
    {
        $fetchData = RequestNcpo::latest()
        ->myApprovals()
        ->paginate(config('app.pagination.per_page', 10));
        return RequestNcpoListingResource::collection($fetchData)
        ->additional([
            "success" => true,
            "message" => "Request NCPO Approvals Successfully Fetched.",
        ]);
    }
}
