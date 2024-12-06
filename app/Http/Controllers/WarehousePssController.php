<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWarehousePssRequest;
use App\Http\Requests\UpdateWarehousePssRequest;
use App\Http\Resources\WarehousePssResource;
use App\Http\Resources\WarehouseResource;
use App\Models\Warehouse;
use App\Models\WarehousePss;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class WarehousePssController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $main = WarehousePss::paginate(10);
        $collection = WarehousePssResource::collection($main)->response()->getData(true);

        return new JsonResponse([
            "success" => true,
            "message" => "Successfully fetched.",
            "data" => $collection,
        ], JsonResponse::HTTP_OK);
    }

    public function get()
    {
        $main = WarehousePss::get();
        $collection = WarehousePssResource::collection($main)->response()->getData(true);

        return new JsonResponse([
            "success" => true,
            "message" => "Successfully fetched.",
            "data" => $collection,
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWarehousePssRequest $request, WarehousePss $resource)
    {
        $saved = $resource->create($request->validated());
        return response()->json([
            'message' => $saved ? 'Warehouse PSS has successfully created.' : 'Failed to create Warehouse PSS.',
            'success' => (bool) $saved,
            'data' => $saved ?? null,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(WarehousePss $resource)
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

    public function update(UpdateWarehousePssRequest $request, Warehouse $warehouse_id)
    {
        $userIds = $request->input('user_ids');
        $currentUserIds = $warehouse_id->warehousePss->pluck('user_id')->toArray();
        if (!empty($userIds)) {
            $intUserIds = array_map('intval', $userIds);
            if ($intUserIds === $currentUserIds) {
                return response()->json([
                    'message' => 'The user(s) are already assigned.',
                    'success' => false,
                    "data" => new WarehouseResource($warehouse_id)
                ]);
            }
            DB::transaction(function () use ($userIds, $warehouse_id) {
                WarehousePss::where("warehouse_id", $warehouse_id->id)->whereNotIn('user_id', $userIds)->delete();
                foreach ($userIds as $id) {
                    $exists = WarehousePss::where([
                        ['warehouse_id', "=", $warehouse_id->id],
                        ['user_id', "=", $id],
                    ])->exists();
                    if (!$exists) {
                        WarehousePss::create([
                            'warehouse_id' => $warehouse_id->id,
                            'user_id' => $id,
                        ]);
                    }
                }
            });
            return response()->json([
                'message' => 'Successfully assigned new PSS',
                'success' => true,
                "data" => new WarehouseResource($warehouse_id->load('warehousePss'))
            ]);
        }
        return response()->json([
            "message" => "Failed to assigned.",
            "success" => false,
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WarehousePss $resource)
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
            'message' => $deleted ? 'Warehouse PSS successfully deleted.' : 'Failed to delete Warehouse PSS.',
            'success' => $deleted,
            'data' => $resource
        ]);
    }
}
