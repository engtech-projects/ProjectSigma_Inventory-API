<?php

namespace App\Http\Controllers;

use App\Enums\RequestApprovalStatus;
use App\Models\WarehouseTransaction;
use App\Http\Requests\StoreWarehouseTransactionRequest;
use App\Http\Resources\WarehouseTransactionResource;
use App\Models\WarehouseTransactionItem;
use App\Notifications\WarehouseTransactionForApprovalNotification;
use App\Traits\HasApproval;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseTransactionController extends Controller
{
    use HasApproval;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $main = WarehouseTransaction::with('items')->paginate(10);
        $collection = WarehouseTransactionResource::collection($main)->response()->getData(true);

        return new JsonResponse([
            "success" => true,
            "message" => "Successfully fetched.",
            "data" => $collection,
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWarehouseTransactionRequest $request)
    {
        $attributes = $request->validated();
        $attributes['request_status'] = $attributes['request_status'] ?? RequestApprovalStatus::PENDING;
        $attributes['created_by'] = auth()->user()->id;


        DB::transaction(function () use (&$warehouseTransaction, $attributes, $request) {
            $warehouseTransaction = WarehouseTransaction::create([
                'warehouse_id' => $attributes['warehouse_id'],
                'transaction_type' => $attributes['transaction_type'],
                'charging_type' => $attributes['charging_type'],
                'charging_id' => $attributes['charging_id'],
                'approvals' => $attributes['approvals'],
                'metadata' => $attributes['metadata'] ?? [],
                'created_by' => $attributes['created_by'],
                'request_status' => $attributes['request_status'],
            ]);

            foreach ($attributes['items'] as $transactionData) {
                $transactionData['warehouse_transaction_id'] = $warehouseTransaction->id;
                $transactionData['metadata'] = array_merge(
                    $attributes['metadata'] ?? [],
                    [
                        'specification' => $transactionData['specification'] ?? null,
                        'actual_brand_purchased' => $transactionData['actual_brand_purchased'] ?? null,
                        'unit_price' => $transactionData['unit_price'] ?? 0,
                        'status' => $transactionData['status'] ?? null,
                        'remarks' => $transactionData['remarks'] ?? null,
                    ]
                );
                WarehouseTransactionItem::create($transactionData);
            }

            if ($warehouseTransaction->getNextPendingApproval()) {
                $warehouseTransaction->notify(new WarehouseTransactionForApprovalNotification($request->bearerToken(), $warehouseTransaction));
            }

        });

        return response()->json([
            'success' => true,
            'message' => 'Transaction Successfully Saved.',
            'data' => [
                'transaction' => $warehouseTransaction,
                'items' => $attributes['items'],  // Include the items in the response
            ]
        ], JsonResponse::HTTP_OK);
    }


    /**
     * Display the specified resource.
     */
    public function show(WarehouseTransaction $resource)
    {
        $resource->load('items');
        return response()->json([
            "message" => "Successfully fetched.",
            "success" => true,
            "data" => new WarehouseTransactionResource($resource)
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WarehouseTransaction $resource)
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
    public function destroy(WarehouseTransaction $resource)
    {
        if (!$resource) {
            return response()->json([
                'message' => 'Warehouse transaction not found.',
                'success' => false,
                'data' => null
            ], 404);
        }

        $deleted = $resource->delete();

        $response = [
            'message' => $deleted ? 'Warehouse transaction successfully deleted.' : 'Failed to delete Warehouse transaction.',
            'success' => $deleted,
            'data' => $resource
        ];

        return response()->json($response, $deleted ? 200 : 400);
    }
}
