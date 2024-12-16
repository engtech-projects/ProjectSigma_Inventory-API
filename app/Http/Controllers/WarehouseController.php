<?php

namespace App\Http\Controllers;

use App\Enums\AccessibilityInventory;
use App\Enums\UserTypes;
use App\Http\Requests\GetLogsRequest;
use App\Http\Requests\StoreWarehouseRequest;
use App\Http\Requests\UpdateWarehouseRequest;
use App\Http\Resources\WarehouseResource;
use App\Http\Resources\WarehouseStocksResource;
use App\Http\Resources\WarehouseTransactionResource;
use App\Http\Traits\CheckAccessibility;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="user",
 *     description="User related operations"
 * )
 * @OA\Info(
 *     version="1.0",
 *     title="Example API",
 *     description="Example info",
 *     @OA\Contact(name="Swagger API Team")
 * )
 * @OA\Server(
 *     url="https://example.localhost",
 *     description="API server"
 * )
 */

class WarehouseController extends Controller
{
    use CheckAccessibility;

    /**
     * @OA\Get(
     *     path="warehouse/resource",
     *     tags={"Warehouse"},
     *     summary="Get all warehouses",
     *     description="Retrieves a list of all warehouses",
     *     @OA\Response(
     *         response=200,
     *         description="A list of warehouses",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Warehouse"))
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */

    public function index()
    {
        $user = Auth::user();
        $userAccessibilitiesNames = $user->accessibilities_name;
        if ($this->checkUserAccessManual($userAccessibilitiesNames, [AccessibilityInventory::INVENTORY_WAREHOUSE_PSSMANAGER->value])
            || Auth::user()->type == UserTypes::ADMINISTRATOR->value
        ) {
            $main = Warehouse::all();
        } else {
            $main = Warehouse::whereHas('warehousePss', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->get();
        }

        $requestResources = WarehouseResource::collection($main)->collect();
        return response()->json([
            'message' => 'Successfully fetched.',
            'success' => true,
            'data' => $requestResources,
        ]);
    }

    public function get()
    {
        $main = Warehouse::get();
        $requestResources = WarehouseResource::collection($main)->collect();

        return response()->json([
            'message' => 'Successfully fetched.',
            'success' => true,
            'data' => $requestResources,
        ]);

    }


    /**
    * @OA\Post(
    *     path="/warehouse/resource",
    *     tags={"Warehouse"},
    *     summary="Create a new warehouse",
    *     @OA\RequestBody(
    *         required=true,
    *         @OA\JsonContent(ref="#/components/schemas/Warehouse")
    *     ),
    *     @OA\Response(
    *         response=201,
    *         description="Warehouse created",
    *         @OA\JsonContent(ref="#/components/schemas/Warehouse")
    *     )
    * )
    */
    public function store(StoreWarehouseRequest $request, Warehouse $resource)
    {
        $saved = $resource->create($request->validated());
        return response()->json([
            'message' => $saved ? 'Warehouse has successfully created.' : 'Failed to create warehouse.',
            'success' => (bool) $saved,
            'data' => $saved ?? null,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Warehouse $warehouse_id)
    {
        $user = Auth::user();
        $userAccessibilitiesNames = $user->accessibilities_name;
        if ($this->checkUserAccessManual($userAccessibilitiesNames, [AccessibilityInventory::INVENTORY_WAREHOUSE_PSSMANAGER->value]) || $warehouse_id->warehousePss->contains('user_id', $user->id)
            || Auth::user()->type == UserTypes::ADMINISTRATOR->value
        ) {
            return response()->json([
                "message" => "Successfully fetched.",
                "success" => true,
                "warehouse" => new WarehouseResource($warehouse_id)
            ]);
        }

        return response()->json([
            "message" => "Unauthorized Access.",
            "success" => false
        ], 403);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWarehouseRequest $request, Warehouse $resource)
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
    public function destroy(Warehouse $resource)
    {
        if (!$resource) {
            return response()->json([
                'message' => 'Warehouse not found.',
                'success' => false,
                'data' => null
            ], 404);
        }

        $deleted = $resource->delete();

        return response()->json([
            'message' => $deleted ? 'Warehouse successfully deleted.' : 'Failed to delete warehouse.',
            'success' => $deleted,
            'data' => $resource
        ]);
    }

    public function getLogs(GetLogsRequest $request, $warehouse_id)
    {
        $validated = $request->validated();

        $date_from = $validated['date_from'] ?? null;
        $date_to = $validated['date_to'] ?? null;
        $item_id = $validated['item_id'] ?? null;
        $transaction_type = $validated['transaction_type'] ?? null;

        $warehouse = Warehouse::with(['transactionItems' => function ($query) use ($date_from, $date_to, $item_id, $transaction_type) {
            if ($date_from) {
                $query->where('warehouse_transaction_items.created_at', '>=', Carbon::parse($date_from));
            }
            if ($date_to) {
                $query->where('warehouse_transaction_items.created_at', '<=', Carbon::parse($date_to)->endOfDay());
            }
            if ($item_id) {
                $query->where('warehouse_transaction_items.item_id', $item_id);
            }
            if ($transaction_type) {
                $query->where('warehouse_transactions.transaction_type', $transaction_type);
            }
        }])->findOrFail($warehouse_id);

        return response()->json([
            'success' => true,
            'message' => 'Successfully Fetched.',
            'warehouse' => $warehouse,
        ]);
    }

    public function getStocks($warehouse_id)
    {
        $warehouse = Warehouse::find($warehouse_id);

        if (!$warehouse) {
            return response()->json([
                'message' => 'No data found.',
                'success' => false,
            ]);
        }

        $transactionItems = $warehouse->transactionItems()->with('item')->paginate(10);

        return response()->json([
            'message' => '' . $warehouse->name . ' Warehouse Stocks Successfully fetched.',
            'success' => true,
            'data' => WarehouseStocksResource::collection($transactionItems)->response()->getData(true),
        ]);

    }

}
