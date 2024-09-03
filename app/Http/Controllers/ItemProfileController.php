<?php

namespace App\Http\Controllers;

use App\Models\ItemProfile;
use App\Http\Requests\StoreItemProfileRequest;
use App\Http\Requests\UpdateItemProfileRequest;
use App\Http\Resources\RequestItemProfilingResource;
use App\Http\Services\RequestItemProfilingService;
use Illuminate\Http\JsonResponse;
use App\Traits\HasApproval;

class ItemProfileController extends Controller
{
    use HasApproval;

    protected $requestItemProfilingService;
    public function __construct(RequestItemProfilingService $requestItemProfilingService)
    {
        $this->requestItemProfilingService = $requestItemProfilingService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {

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
        //
    }


    /**
     * Display the specified resource.
     */
    public function show(ItemProfile $resource)
    {
        return response()->json([
            "message" => "Successfully fetched.",
            "success" => true,
            "data" => $resource
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function update(UpdateItemProfileRequest $request, $id)
    {
        //
    }
    public function destroy($id)
    {
        //
    }

    public function activate(ItemProfile $resource)
    {
        $resource->active_status = 'Active';

        if ($resource->save()) {
            return response()->json([
                "message" => "Successfully activated.",
                "success" => true,
                "data" => $resource->refresh()
            ]);
        }

        return response()->json([
            "message" => "Failed to activate.",
            "success" => false,
            "data" => $resource
        ], 400);
    }
    public function deactivate(ItemProfile $resource)
    {
        $resource->active_status = 'Active';

        if ($resource->save()) {
            return response()->json([
                "message" => "Successfully activated.",
                "success" => true,
                "data" => $resource->refresh()
            ]);
        }

        return response()->json([
            "message" => "Failed to activate.",
            "success" => false,
            "data" => $resource
        ], 400);
    }


}
