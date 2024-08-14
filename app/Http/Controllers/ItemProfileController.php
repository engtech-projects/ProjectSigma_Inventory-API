<?php

namespace App\Http\Controllers;

use App\Models\ItemProfile;
use App\Http\Requests\StoreItemProfileRequest;
use App\Http\Requests\UpdateItemProfileRequest;
use App\Http\Resources\ItemProfileResource;

class ItemProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $itemprofile = ItemProfile::paginate(10);
        $data = json_decode('{}');
        $data->message = "Successfully fetched.";
        $data->success = true;
        $data->data = $itemprofile;
        return response()->json($data);
    }

    public function get()
    {
        $main = ItemProfile::get();
        $data = json_decode('{}');
        $data->message = "Successfully fetched.";
        $data->success = true;
        $data->data = $main;
        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreItemProfileRequest $request)
    {
        $itemprofile = new ItemProfile();
        $itemprofile->fill($request->validated());
        $itemprofile->is_approved = false;
        $saved = $itemprofile->save();
        $response = [
            'message' => $saved ? 'Successfully saved.' : 'Save failed.',
            'success' => $saved,
            'data' => $saved ? new ItemProfileResource($itemprofile) : null,
        ];

        return response()->json($response, $saved ? 200 : 400);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $itemprofile = ItemProfile::find($id);
        $data = json_decode('{}');
        if (!is_null($itemprofile)) {
            $data->message = "Successfully fetched.";
            $data->success = true;
            $data->data = $itemprofile;
            return response()->json($data);
        }
        $data->message = "No data found.";
        $data->success = false;
        return response()->json($data, 404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function update(UpdateItemProfileRequest $request, $id)
    {
        $itemprofile = ItemProfile::find($id);
        $data = json_decode('{}');
        if (!is_null($itemprofile)) {
            $itemprofile->fill($request->validated());
            if ($itemprofile->save()) {
                $data->message = "Successfully updated.";
                $data->success = true;
                $data->data = $itemprofile;
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
    public function destroy($id)
    {
        $itemprofile = ItemProfile::find($id);
        $data = json_decode('{}');
        if (!is_null($itemprofile)) {
            if ($itemprofile->delete()) {
                $data->message = "Successfully deleted.";
                $data->success = true;
                $data->data = $itemprofile;
                return response()->json($data);
            }
            $data->message = "Failed to delete.";
            $data->success = false;
            return response()->json($data, 404);
        }
        $data->message = "Failed to delete.";
        $data->success = false;
        return response()->json($data, 404);
    }
}
