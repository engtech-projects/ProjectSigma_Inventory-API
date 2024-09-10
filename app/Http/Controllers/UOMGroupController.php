<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUOMGroupRequest;
use App\Http\Requests\UpdateUOMGroupRequest;
use App\Http\Resources\UOMGroupResource;
use App\Models\UOMGroup;

class UOMGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $uomgroup = UOMGroup::get();
        $data = json_decode('{}');
        $data->message = "Successfully fetched.";
        $data->success = true;
        $data->data = $uomgroup;
        return response()->json($data);
    }

    public function get()
    {
        $main = UOMGroup::get();
        $data = json_decode('{}');
        $data->message = "UOM Group Fetched.";
        $data->success = true;
        $data->data = $main;
        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUOMGroupRequest $request, UOMGroup $resource)
    {
        $saved = $resource->create($request->validated());
        return response()->json([
            'message' => $saved ? 'UOM Group Successfully created.' : 'Failed to create UOM Group.',
            'success' => (bool) $saved,
            'data' => $saved ? new UOMGroupResource($saved) : null,
        ]);
    }

    /**
     * Display the specified resource.
     */

    public function show(UOMGroup $resource)
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
    public function update(UpdateUOMGroupRequest $request, UOMGroup $resource)
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
    public function destroy(UOMGroup $resource)
    {
        if (!$resource) {
            return response()->json([
                'message' => 'Item Profile not found.',
                'success' => false,
                'data' => null
            ], 404);
        }

        $deleted = $resource->delete();

        $response = [
            'message' => $deleted ? 'Item Profile successfully deleted.' : 'Failed to delete Item Profile.',
            'success' => $deleted,
            'data' => $resource
        ];
    }
}
