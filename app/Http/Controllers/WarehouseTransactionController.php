<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatuses;
use App\Http\Resources\WarehouseTransactionResourceList;
use App\Models\WarehouseTransaction;
use App\Http\Requests\StoreWarehouseTransactionRequest;
use App\Http\Resources\WarehouseTransactionResource;
use App\Http\Services\WarehouseTransactionService;
use App\Models\WarehouseTransactionItem;
use App\Notifications\WarehouseTransactionForApprovalNotification;
use App\Traits\HasApproval;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WarehouseTransactionController extends Controller
{
    use HasApproval;
    protected $warehouseTransactionService;
    public function __construct(WarehouseTransactionService $warehouseTransactionService)
    {
        $this->warehouseTransactionService = $warehouseTransactionService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $main = WarehouseTransaction::with(['items.uomRelationship', 'items.item'])->paginate(10);
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
        $attributes['request_status'] = RequestStatuses::PENDING;
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

    public function saveDetails(Request $request, $id)
    {
        try {
            $resource = WarehouseTransaction::findOrFail($id);

            $request->validate([
                'metadata.supplier_id' => 'nullable|integer',
                'metadata.terms_of_payment' => 'nullable|string|max:255',
                'metadata.particulars' => 'nullable|string|max:1000',
                'metadata.reference' => 'nullable|string|max:255',
            ]);

            $metadata = $resource->metadata ?? [];
            $updated = false;
            $updatedFields = [];

            $incomingData = $request->input('metadata', []);

            if (empty($incomingData)) {
                $incomingData = $request->only(['supplier_id', 'terms_of_payment', 'particulars', 'reference']);
            }

            if (isset($incomingData['supplier_id']) && $incomingData['supplier_id'] !== null) {
                $metadata['supplier_id'] = $incomingData['supplier_id'];
                $updated = true;
                $updatedFields[] = 'supplier';
            }

            if (isset($incomingData['terms_of_payment']) && $incomingData['terms_of_payment'] !== null) {
                $metadata['terms_of_payment'] = $incomingData['terms_of_payment'];
                $updated = true;
                $updatedFields[] = 'terms of payment';
            }

            if (isset($incomingData['particulars']) && $incomingData['particulars'] !== null) {
                $metadata['particulars'] = $incomingData['particulars'];
                $updated = true;
                $updatedFields[] = 'particulars';
            }

            if (isset($incomingData['reference']) && $incomingData['reference'] !== null) {
                $metadata['reference'] = $incomingData['reference'];
                $updated = true;
                $updatedFields[] = 'reference';
            }

            if (!$updated) {
                return response()->json([
                    'message' => 'No valid fields provided for update.',
                    'success' => false,
                    'data' => $resource,
                    'received_data' => $request->all(),
                    'debug_info' => [
                        'has_metadata' => $request->has('metadata'),
                        'metadata_content' => $request->input('metadata'),
                        'all_input' => $request->all()
                    ]
                ], 400);
            }

            $resource->metadata = $metadata;

            if ($resource->save()) {
                $fieldsList = implode(', ', $updatedFields);
                return response()->json([
                    'message' => "Successfully auto-saved: {$fieldsList}.",
                    'success' => true,
                    'data' => $resource->refresh(),
                    'updated_fields' => $updatedFields
                ], 200);
            }

            return response()->json([
                'message' => 'Failed to auto-save changes.',
                'success' => false,
                'data' => $resource
            ], 500);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Warehouse transaction not found.',
                'success' => false,
                'data' => null
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while auto-saving: ' . $e->getMessage(),
                'success' => false,
                'data' => null
            ], 500);
        }
    }

    public function allRequests()
    {
        $myRequest = $this->warehouseTransactionService->getAllRequest();
        $requestResources = WarehouseTransactionResourceList::collection($myRequest)->response()->getData(true);
        return new JsonResponse([
            'message' => 'All Request Fetched.',
            'success' => true,
            'data' => $requestResources
        ]);
    }
    public function getTransactionsByWarehouse(int $warehouse_id)
    {
        $main = WarehouseTransaction::with(['items.uomRelationship', 'items.item', 'supplier'])
            ->where('warehouse_id', $warehouse_id)
            ->paginate(10);
        $collection = WarehouseTransactionResource::collection($main)->response()->getData(true);
        return response()->json([
            "message" => "Warehouse Transactions Successfully Fetched.",
            "success" => true,
            "data" => $collection,
        ], JsonResponse::HTTP_OK);
    }
}
