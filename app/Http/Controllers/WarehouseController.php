<?php

namespace App\Http\Controllers;

use App\Enums\AccessibilityInventory;
use App\Http\Requests\GetLogsRequest;
use App\Http\Requests\StoreWarehouseRequest;
use App\Http\Requests\UpdateWarehouseRequest;
use App\Http\Resources\WarehouseLogsResource;
use App\Http\Resources\WarehouseResource;
use App\Http\Resources\WarehouseStocksResource;
use App\Http\Traits\CheckAccessibility;
use App\Models\SetupWarehouses;
use App\Models\WarehouseStockTransactions;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class WarehouseController extends Controller
{
    use CheckAccessibility;

    public function index()
    {
        $user = Auth::user();
        $isPssManager = $this->checkUserAccess([AccessibilityInventory::INVENTORY_WAREHOUSE_PSSMANAGER->value]);
        $warehouses = SetupWarehouses::when(!$isPssManager, function ($query) use ($user) {
            $query->whereHas('warehousePss', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        })
        ->get();
        return response()->json([
            'message' => 'Successfully fetched.',
            'success' => true,
            'data' => WarehouseResource::collection($warehouses),
        ]);
    }

    public function get()
    {
        $main = SetupWarehouses::get();
        $requestResources = WarehouseResource::collection($main)->collect();

        return response()->json([
            'message' => 'Successfully fetched.',
            'success' => true,
            'data' => $requestResources,
        ]);
    }
    public function store(StoreWarehouseRequest $request, SetupWarehouses $resource)
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
    public function show(SetupWarehouses $warehouse)
    {
        $user = Auth::user();
        $warehouse->load('warehousePss');
        $isPssUser = $warehouse->warehousePss?->id === $user->id;
        $isPssManager = $this->checkUserAccess([AccessibilityInventory::INVENTORY_WAREHOUSE_PSSMANAGER->value]);
        if ($isPssManager || $isPssUser) {
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

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWarehouseRequest $request, SetupWarehouses $resource)
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
    public function destroy(SetupWarehouses $resource)
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
        $warehouse = WarehouseStockTransactions::with(['item', 'uomRelationship', 'transaction'])
            ->where('warehouse_id', $warehouse_id)
            ->whereBetween('created_at', [$parseDateFrom, $parseDateTo])->get()
            ->where("referenceable_type", $transaction_type)
            ->latest()
            ->get();
        $returnData = WarehouseLogsResource::collection($warehouse);
        return new JsonResponse([
            "success" => true,
            "message" => "Successfully fetched.",
            "data" => $returnData
        ], JsonResponse::HTTP_OK);
    }

    public function getStocks(SetupWarehouses $warehouse)
    {
        $transactionItems = $warehouse->stockSummary()->with('item')->paginate(10);
        return response()->json([
            'message' => '' . $warehouse->name . ' Warehouse Stocks Successfully fetched.',
            'success' => true,
            'data' => WarehouseStocksResource::collection($transactionItems)->response()->getData(true),
        ]);
    }
}
