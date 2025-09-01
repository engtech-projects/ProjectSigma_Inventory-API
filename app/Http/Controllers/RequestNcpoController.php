<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNcpoRequest;
use App\Http\Resources\RequestNcpoListingResource;
use App\Http\Resources\RequestNcpoResource;
use App\Models\RequestNCPO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            $itemsData = [];
            foreach ($validated['items'] as $item) {
                $newTotal = ($item['changed_qty'] ?? 0) * ($item['changed_unit_price'] ?? 0);
                if ($item['cancel_item'] ?? false) {
                    $newTotal = 0;
                }
                $itemsData[] = array_merge($item, [
                    'request_ncpo_id' => $ncpo->id,
                    'new_total' => $newTotal,
                ]);
            }
            $ncpo->ncpoItems()->createMany($itemsData);
            return $ncpo;
        });
        //for notification
        // if ($ncpo->getNextPendingApproval()) {
        //     $ncpo->notify(new RequestNCPOForApprovalNotification($request->bearerToken(), $ncpo));
        // }
        return new JsonResponse([
            'success' => true,
            'message' => 'Request NCPO created successfully.',
            'data' => new RequestNcpoResource($ncpo),
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(RequestNcpo $request_ncpo)
    {
        $request_ncpo->load([
            'ncpoItems.itemProfile',
            'purchaseOrder',
        ]);
        return new JsonResponse([
            'success' => true,
            'message' => 'Request NCPO retrieved successfully.',
            'data' => new RequestNcpoResource($request_ncpo),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RequestNcpo $request_ncpo)
    {
        //
    }
}
