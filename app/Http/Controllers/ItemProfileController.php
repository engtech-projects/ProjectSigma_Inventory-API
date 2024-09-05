<?php

namespace App\Http\Controllers;

use App\Models\ItemProfile;
use App\Http\Requests\StoreItemProfileRequest;
use App\Http\Requests\UpdateItemProfileRequest;
use App\Http\Resources\ItemProfileResource;
use App\Http\Services\RequestItemProfilingService;
use App\Traits\HasApproval;
use App\Utils\PaginateResourceCollection;

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
        $main = ItemProfile::get();
        $paginated = PaginateResourceCollection::paginate($main);
        $data = json_decode('{}');
        $data->message = "Request Item Profiling Successfully Fetched.";
        $data->success = true;
        $data->data = $paginated;
        return response()->json($data);
    }

    public function get()
    {
        $main = ItemProfile::where("is_approved", 1)->get();
        $requestResources = ItemProfileResource::collection($main)->collect();
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
        if ($resource->active_status === 'Active') {
            return response()->json([
                'message' => "Item profile is already active.",
                'item_profile' => $resource
            ], 200);
        }
        $resource->active_status = 'Active';
        $resource->save();

        return response()->json([
            'message' => 'Item profile activated successfully.',
            'item_profile' => $resource
        ]);
    }

    public function deactivate(ItemProfile $resource)
    {
        if ($resource->active_status === 'Inactive') {
            return response()->json([
                'message' => 'Item profile is already inactive.',
                'item_profile' => $resource
            ], 200);
        }
        $resource->active_status = 'Inactive';
        $resource->save();

        return response()->json([
            'message' => 'Item profile deactivated successfully.',
            'item_profile' => $resource
        ]);
    }


}
