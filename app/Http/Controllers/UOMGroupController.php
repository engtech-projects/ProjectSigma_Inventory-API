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
        $uomgroup = UOMGroup::paginate(10);
        $data = json_decode('{}');
        $data->message = "Successfully fetched.";
        $data->success = true;
        $data->data = $uomgroup;
        return response()->json($data);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
    public function store(StoreUOMGroupRequest $request)
    {
        $uomGroupData = $request->validated();
        // $uomGroupData['is_standard'] = false;

        $uomGroup = UOMGroup::create($uomGroupData);

        $response = [
            'message' => $uomGroup ? 'Successfully saved.' : 'Save failed.',
            'success' => (bool) $uomGroup,
            'data' => $uomGroup ? new UOMGroupResource($uomGroup) : null,
        ];

        return response()->json($response, $uomGroup ? 200 : 400);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $uomGroup = UOMGroup::find($id);

        if ($uomGroup) {
            return response()->json([
                'success' => true,
                'message' => 'Successfully fetched.',
                'data' => new UOMGroupResource($uomGroup)
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data found.'
            ], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UOMGroup $uOMGroup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUOMGroupRequest $request, $id)
    {
        $uomGroup = UOMGroup::find($id);
        $data = json_decode('{}');
        if (!is_null($uomGroup)) {
            $uomGroup->fill($request->validated());
            if ($uomGroup->save()) {
                $data->message = "Successfully updated.";
                $data->success = true;
                $data->data = $uomGroup;
                return response()->json($data);
            }
            $data->message = "Failed to update.";
            $data->success = false;
            return response()->json($data, 400);
        }
        $data->message = "Failed to update.";
        $data->success = false;
        return response()->json($data, 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $uomGroup = UOMGroup::find($id);
        $data = json_decode('{}');
        if (!is_null($uomGroup)) {
            if ($uomGroup->delete()) {
                $data->message = "Successfully deleted.";
                $data->success = true;
                $data->data = $uomGroup;
                return response()->json($data);
            }
            $data->message = "Failed to delete.";
            $data->success = false;
            return response()->json($data, 400);
        }
        $data->message = "Failed to delete.";
        $data->success = false;
        return response()->json($data, 404);
    }
}
