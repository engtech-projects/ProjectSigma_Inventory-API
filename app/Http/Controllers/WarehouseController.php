<?php

namespace App\Http\Controllers;

use App\Enums\AccessibilityInventory;
use App\Enums\UserTypes;
use App\Http\Requests\GetLogsRequest;
use App\Http\Requests\StoreWarehouseRequest;
use App\Http\Requests\UpdateWarehouseRequest;
use App\Http\Resources\WarehouseLogsResource;
use App\Http\Resources\WarehouseMaterialsReceivingResource;
use App\Http\Resources\WarehouseResource;
use App\Http\Resources\WarehouseStocksResource;
use App\Http\Traits\CheckAccessibility;
use App\Models\Warehouse;
use App\Models\WarehouseTransactionItem;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class WarehouseController extends Controller
{
    use CheckAccessibility;

    public function index()
    {
        $user = Auth::user();
        $userAccessibilitiesNames = $user->accessibilities_name;

        $main = (
            $this->checkUserAccessManual($userAccessibilitiesNames, [
                AccessibilityInventory::INVENTORY_WAREHOUSE_PSSMANAGER->value
            ]) || $user->type === UserTypes::ADMINISTRATOR->value
        )
            ? Warehouse::all()
            : Warehouse::whereHas('warehousePss', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->get();

        return response()->json([
            'message' => 'Successfully fetched.',
            'success' => true,
            'data' => WarehouseResource::collection($main),
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
    public function show($warehouse_id)
    {
        $user = Auth::user();
        $userAccessibilitiesNames = $user->accessibilities_name;

        // Eager-load warehousePss relation
        $warehouse = Warehouse::with('warehousePss')->findOrFail($warehouse_id);

        if (
            $this->checkUserAccessManual($userAccessibilitiesNames, [AccessibilityInventory::INVENTORY_WAREHOUSE_PSSMANAGER->value])
            || optional($warehouse->warehousePss)->id
            || $user->type == UserTypes::ADMINISTRATOR->value
        ) {
            return response()->json([
                "message" => "Successfully fetched.",
                "success" => true,
                "warehouse" => new WarehouseResource($warehouse)
            ]);
        }

        return response()->json([
            "message" => "Unauthorized Access.",
            "success" => false
        ], 403);
    }

    public function withMaterialsReceiving(Warehouse $warehouse_id)
    {
        return response()->json([
            "message" => "Materials Receiving under " . $warehouse_id->name . " Warehouse successfully fetched.",
            "success" => true,
            "warehouse" => new WarehouseMaterialsReceivingResource($warehouse_id)
        ]);
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
        $transaction_type = $validated['transaction_type'] ?? null;

        $parseDateFrom = $date_from ? Carbon::parse($date_from)->startOfDay() : null;
        $parseDateTo = $date_to ? Carbon::parse($date_to)->endOfDay() : null;

        $warehouse = WarehouseTransactionItem::with(['item', 'uomRelationship', 'transaction'])->whereHas(
            "transaction",
            function ($query) use ($warehouse_id, $parseDateFrom, $parseDateTo, $transaction_type) {
                $query->where('warehouse_id', $warehouse_id);
                if (!is_null($transaction_type)) {
                    $query->where('transaction_type', $transaction_type);
                }
                if (!is_null($parseDateFrom) && !is_null($parseDateTo)) {
                    $query->betweenDates($parseDateFrom, $parseDateTo);
                }
            }
        )->orderBy('created_at', 'desc')->get();

        $returnData = WarehouseLogsResource::collection($warehouse);

        return new JsonResponse([
            "success" => true,
            "message" => "Successfully fetched.",
            "data" => $returnData
        ], JsonResponse::HTTP_OK);
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
