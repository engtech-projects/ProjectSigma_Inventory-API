<?php

namespace App\Http\Controllers;

use App\Models\WarehouseTransactionItem;
use App\Http\Requests\StoreWarehouseTransactionItemRequest;
use App\Http\Requests\UpdateWarehouseTransactionItemRequest;
use App\Http\Resources\WarehouseTransactionItemResource;
use App\Utils\PaginateResourceCollection;
use Illuminate\Http\Request;

class WarehouseTransactionItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = WarehouseTransactionItem::get();
        $requestResources = WarehouseTransactionItemResource::collection($items)->collect();
        $paginated = PaginateResourceCollection::paginate($requestResources);

        return response()->json([
            'message' => 'Successfully fetched.',
            'success' => true,
            'data' => $paginated,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWarehouseTransactionItemRequest $request, WarehouseTransactionItem $resource)
    {
        $saved = $resource->create($request->validated());
        return response()->json([
            'message' => $saved ? 'Item(s) successfully created.' : 'Failed to create item(S).',
            'success' => (bool) $saved,
            'data' => $saved ?? null,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(WarehouseTransactionItem $resource)
    {
        return response()->json([
            "message" => "Successfully fetched.",
            "success" => true,
            "data" => $resource
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWarehouseTransactionItemRequest $request, WarehouseTransactionItem $resource)
    {
        $resource->fill($request->validated());
        if ($resource->save()) {
            return response()->json([
                "message" => "Successfully updated.",
                "success" => true,
                "data" => $resource->refresh()
            ]);
        }
        return response()->json([
            "message" => "Failed to update.",
            "success" => false,
            "data" => $resource
        ], 400);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WarehouseTransactionItem $resource)
    {
        if (!$resource) {
            return response()->json([
                'message' => 'Item(s) not found.',
                'success' => false,
                'data' => null
            ], 404);
        }

        $deleted = $resource->delete();

        $response = [
            'message' => $deleted ? 'An item(s) has successfully deleted.' : 'Failed to delete item(s).',
            'success' => $deleted,
            'data' => $resource
        ];

        return response()->json($response, $deleted ? 200 : 400);
    }
}
