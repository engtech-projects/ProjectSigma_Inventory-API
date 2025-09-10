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
        if ($ncpo->getNextPendingApproval()) {
            $ncpo->notify(new RequestNcpoForApprovalNotification($request->bearerToken(), $ncpo));
        }
        $ncpoResource = RequestNcpoResource::make($ncpo);
        return $ncpoResource->additional([
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
}
