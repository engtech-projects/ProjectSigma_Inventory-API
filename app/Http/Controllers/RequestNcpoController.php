<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNcpoRequest;
use App\Http\Resources\RequestNcpoDetailedResource;
use App\Http\Resources\RequestNcpoListingResource;
use App\Http\Resources\RequestNcpoResource;
use App\Models\RequestNCPO;
use App\Notifications\RequestNCPOForApprovalNotification;
use Illuminate\Support\Facades\DB;

class RequestNcpoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $requestNCPOs = RequestNCPO::paginate(config('app.pagination.per_page', 15));
        return RequestNcpoListingResource::collection($requestNCPOs)
        ->additional([
            'message' => 'Request Purchase Orders retrieved successfully.',
            'success' => true,
        ]);
    }

    public function store(StoreNcpoRequest $request)
    {
        $validated = $request->validated();
        $ncpo = DB::transaction(function () use ($validated) {
            $ncpoNo = RequestNCPO::generateReferenceNumber(
                'ncpo_no',
                fn ($prefix, $datePart, $number) => "{$prefix}-{$datePart}-{$number}",
                ['prefix' => 'NCPO', 'dateFormat' => 'Y-m']
            );
            $ncpo = RequestNCPO::create([
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
            $ncpo->notify(new RequestNCPOForApprovalNotification($request->bearerToken(), $ncpo));
        }
        $ncpoResource = RequestNcpoResource::make($ncpo);
        return $ncpoResource->additional([
            'message' => 'Request NCPO created successfully.',
            'success' => true,
        ]);
    }

    /**
     * Display the specified resource.
     */
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
