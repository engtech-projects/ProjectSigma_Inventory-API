<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWarehouseRequest;
use App\Http\Requests\UpdateWarehouseRequest;
use App\Models\Warehouse;
use App\Utils\PaginateResourceCollection;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $main = Warehouse::get();
        $paginated = PaginateResourceCollection::paginate($main);
        $data = json_decode('{}');
        $data->message = "Request Warehouse Successfully Fetched.";
        $data->success = true;
        $data->data = $paginated;
        return response()->json($data);
    }

    public function get()
    {
        $main = Warehouse::get();
        $data = json_decode('{}');
        $data->message = "Successfully fetched.";
        $data->success = true;
        $data->data = $main;
        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
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
    public function show(Warehouse $resource)
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
