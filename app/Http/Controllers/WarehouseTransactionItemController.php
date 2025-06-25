<?php

namespace App\Http\Controllers;

use App\Http\Requests\RejectWarehouseTransactionItemRequest;
use App\Http\Requests\StoreWarehouseTransactionAllItemRequest;
use App\Models\WarehouseTransactionItem;
use App\Http\Requests\StoreWarehouseTransactionItemRequest;
use App\Http\Requests\UpdateWarehouseTransactionItemRequest;
use App\Http\Resources\WarehouseTransactionItemResource;
use Illuminate\Http\JsonResponse;

class WarehouseTransactionItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $main = WarehouseTransactionItem::with('item', 'uomRelationship')->paginate(10);
        $collection = WarehouseTransactionItemResource::collection($main)->response()->getData(true);

        return new JsonResponse([
            "message" => "Successfully fetched.",
            "success" => true,
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

    public function acceptAll(StoreWarehouseTransactionAllItemRequest $request, WarehouseTransactionItem $resource)
    {
        $quantity = $resource->quantity;
        $accepted_quantity = $quantity;
        $unit_price = $request->input('unit_price');
        $actual_brand_purchase = $request->input('actual_brand_purchase');
        $specification = $request->input('specification');
        $grand_total = $request->input('grand_total');

        $metadata = $resource->metadata ?? [];
        $metadata['status'] = 'Accepted';
        $metadata['remarks'] = 'Accepted';
        $metadata['unit_price'] = $unit_price;
        $metadata['accepted_quantity'] = $accepted_quantity;
        $metadata['actual_brand_purchase'] = $actual_brand_purchase;
        $metadata['specification'] = $specification;
        $metadata['grand_total'] = $grand_total;

        $message = (isset($resource->metadata['status']) && $resource->metadata['status'] === 'Accepted')
            ? "Accepted quantity and remarks have been updated."
            : "Item has been successfully accepted with unit price.";

        $resource->update([
            'metadata' => $metadata,
            'quantity' => $quantity,
        ]);

        return response()->json([
            'message' => $message,
            'data' => $resource
        ], 200);
    }

    public function acceptWithDetails(StoreWarehouseTransactionItemRequest $request, WarehouseTransactionItem $resource)
    {
        $quantity = $resource->quantity;
        $accepted_quantity = $request->input('accepted_quantity', $quantity);
        $remarks = $request->input('remarks');
        $unit_price = $request->input('unit_price');
        $actual_brand_purchase = $request->input('actual_brand_purchase');
        $specification = $request->input('specification');
        $grand_total = $request->input('grand_total');

        $metadata = $resource->metadata ?? [];
        $metadata['status'] = 'Accepted';
        $metadata['remarks'] = $remarks;
        $metadata['unit_price'] = $unit_price;
        $metadata['accepted_quantity'] = $accepted_quantity;
        $metadata['actual_brand_purchase'] = $actual_brand_purchase;
        $metadata['specification'] = $specification;
        $metadata['grand_total'] = $grand_total;

        $message = (isset($resource->metadata['status']) && $resource->metadata['status'] === 'Accepted')
            ? "Accepted quantity, actual brand purchase, unit price, and remarks have been updated."
            : "Item has been successfully accepted.";

        $resource->update([
            'metadata' => $metadata,
            'quantity' => $quantity,

        ]);

        return response()->json([
            'message' => $message,
            'data' => $resource
        ], 200);
    }

    public function reject(RejectWarehouseTransactionItemRequest $request, WarehouseTransactionItem $resource)
    {
        if (isset($resource->metadata['status']) && $resource->metadata['status'] === 'Rejected') {
            return response()->json([
                'message' => "Item has already been rejected.",
                'data' => $resource
            ], 200);
        }

        $remarks = $request->input('remarks');

        $metadata = $resource->metadata ?? [];
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
