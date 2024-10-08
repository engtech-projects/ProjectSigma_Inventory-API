<?php

namespace App\Http\Controllers;

use App\Enums\AccessibilityInventory;
use App\Http\Requests\StoreWarehouseRequest;
use App\Http\Requests\UpdateWarehouseRequest;
use App\Http\Resources\WarehouseResource;
use App\Http\Traits\CheckAccessibility;
use App\Models\Warehouse;
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

        if ($this->checkUserAccessManual($userAccessibilitiesNames, [AccessibilityInventory::INVENTORY_WAREHOUSE_PSSMANAGER->value])) {
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

        if ($this->checkUserAccess([AccessibilityInventory::INVENTORY_WAREHOUSE_PSSMANAGER->value]) || $warehouse_id->warehousePss->contains('user_id', $user->id)) {

            return response()->json([
                "message" => "Successfully fetched.",
                "success" => true,
                "data" => new WarehouseResource($warehouse_id)
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


}
