<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWarehousePssRequest;
use App\Http\Requests\UpdateWarehousePssRequest;
use App\Http\Resources\WarehousePssResource;
use App\Http\Services\HrmsService;
use App\Models\Warehouse;
use App\Models\WarehousePss;
use App\Utils\PaginateResourceCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class WarehousePssController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $main = WarehousePss::get();
        $paginated = PaginateResourceCollection::paginate($main);
        $data = json_decode('{}');
        $data->message = "Request Warehouse PSS Successfully Fetched.";
        $data->success = true;
        $data->data = $paginated;
        return response()->json($data);
    }

    public function get()
    {
        $main = WarehousePss::get();
        $requestResources = WarehousePssResource::collection($main)->collect();
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
   public function update(UpdateWarehousePssRequest $request, WarehousePss $warehousePss)
    {
        $warehousePss->fill($request->validated());

        $warehousePss->user_id = $request->input('id');

        if ($warehousePss->save()) {
            return response()->json([
                "message" => "PSS Successfully Assigned.",
                "success" => true,
                "data" => $warehousePss->refresh()
            ]);
        }

        return response()->json([
            "message" => "Failed to Assign PSS.",
            "success" => false,
            "data" => $warehousePss
        ], 400);
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
