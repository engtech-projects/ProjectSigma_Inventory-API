<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateNcpoRequest;
use App\Http\Resources\RequestNcpoDetailedResource;
use App\Http\Resources\RequestNcpoListingResource;
use App\Http\Resources\RequestNcpoResource;
use App\Models\RequestNcpo;
use Illuminate\Support\Facades\DB;

class RequestNcpoController extends Controller
{
    public function index()
    {
        $requestNCPOs = RequestNcpo::paginate(config('app.pagination.per_page', 15));
        return RequestNcpoListingResource::collection($requestNCPOs)
        ->additional([
            'message' => 'Request Purchase Orders retrieved successfully.',
            'success' => true,
        ]);
    }
    public function update(UpdateNcpoRequest $request, RequestNcpo $resource)
    {
        $validated = $request->validated();
        DB::transaction(function () use ($validated, $resource) {
            $resource->update([
                'date'          => $validated['date'],
                'justification' => $validated['justification'],
                'approvals'     => $validated['approvals'],
            ]);
            foreach ($validated['items'] as $itemData) {
                $existingItem = $resource->items()
                    ->where('item_id', $itemData['item_id'])
                    ->first();
                if ($existingItem) {
                    $existingItem->update($itemData);
                } else {
                    $resource->items()->create([
                        ...$itemData,
                        'request_ncpo_id' => $resource->id,
                    ]);
                }
            }
        });
        $resource->load('items');
        return RequestNcpoResource::make($resource)->additional([
            'message' => 'Request NCPO updated successfully.',
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
