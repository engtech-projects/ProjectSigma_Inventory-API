<?php

namespace App\Http\Controllers;

use App\Models\WarehouseTransactionItem;
use App\Http\Requests\StoreWarehouseTransactionItemRequest;
use App\Http\Requests\UpdateWarehouseTransactionItemRequest;
use App\Http\Resources\WarehouseTransactionItemResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarehouseTransactionItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $main = WarehouseTransactionItem::paginate(10);
        $collection = WarehouseTransactionItemResource::collection($main)->response()->getData(true);

        return new JsonResponse([
            "success" => true,
            "message" => "Successfully fetched.",
            "data" => $collection,
        ], JsonResponse::HTTP_OK);
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

    public function acceptAll(Request $request, WarehouseTransactionItem $resource)
    {
        $quantity = max($resource->qty, $request->input('quantity'));
        $unit_price = $request->input('unit_price');
        $actual_brand_purchase = $request->input('actual_brand_purchase');

        // Retrieve existing metadata and update fields
        $metadata = $resource->metadata;
        $metadata['status'] = 'Accepted';
        $metadata['remarks'] = 'Accepted';
        $metadata['unit_price'] = $unit_price;
        $metadata['actual_brand_purchase'] = $actual_brand_purchase;

        // Determine response message based on existing status
        $message = ($resource->metadata['status'] === 'Accepted')
            ? "Accepted quantity and remarks have been updated."
            : "Item has been successfully accepted with unit price.";

        // Update resource
        $resource->update([
            'metadata' => $metadata,
            'quantity' => $quantity
        ]);

        return response()->json([
            'message' => $message,
            'data' => $resource
        ], 200);
    }

    public function acceptWithDetails(Request $request, WarehouseTransactionItem $resource)
    {
        $quantity = $request->input('quantity');
        $remarks = $request->input('remarks');
        $unit_price = $request->input('unit_price');
        $actual_brand_purchase = $request->input('actual_brand_purchase');

        // Retrieve existing metadata and update fields
        $metadata = $resource->metadata;
        $metadata['status'] = 'Accepted';
        $metadata['remarks'] = $remarks;
        $metadata['unit_price'] = $unit_price;
        $metadata['actual_brand_purchase'] = $actual_brand_purchase;

        // Determine response message based on existing status
        $message = ($resource->metadata['status'] === 'Accepted')
            ? "Accepted quantity, unit price, and remarks have been updated."
            : "Item has been successfully accepted with unit price.";

        // Update resource
        $resource->update([
            'metadata' => $metadata,
            'quantity' => $quantity
        ]);

        return response()->json([
            'message' => $message,
            'data' => $resource
        ], 200);
    }

    public function reject(Request $request, WarehouseTransactionItem $resource)
    {
        if ($resource->metadata['status'] === 'Rejected') {
            return response()->json([
                'message' => "Item has already been rejected.",
                'data' => $resource
            ], 200);
        }

        $remarks = $request->input('remarks');

        $metadata = $resource->metadata;
        $metadata['status'] = 'Rejected';
        $metadata['remarks'] = $remarks;

        $resource->update([
            'metadata' => $metadata,
        ]);

        return response()->json([
            'message' => "Item has been successfully rejected.",
            'data' => $resource
        ]);
    }


}
