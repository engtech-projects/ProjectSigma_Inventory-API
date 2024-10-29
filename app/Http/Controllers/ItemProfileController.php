<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchItemProfile;
use App\Http\Requests\SearchUOM;
use App\Models\ItemProfile;
use App\Http\Requests\StoreItemProfileRequest;
use App\Http\Requests\UpdateItemProfileRequest;
use App\Http\Resources\ItemProfileResource;
use App\Http\Resources\SearchedItemsResource;
use App\Utils\PaginateResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ItemProfileController extends Controller
{
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
        $main = ItemProfile::isApproved()->get();
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

    public function store(StoreItemProfileRequest $request, ItemProfile $resource)
    {
        $saved = $resource->create($request->validated());
        return response()->json([
            'message' => $saved ? 'Item Profile Successfully created.' : 'Failed to create Item Profile.',
            'success' => (bool) $saved,
            'data' => $saved ?? null,
        ]);
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
    public function update(UpdateItemProfileRequest $request, ItemProfile $resource)
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
    public function destroy(ItemProfile $resource)
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

        return response()->json($response, $deleted ? 200 : 400);
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

    public function search(SearchItemProfile $request)
    {
        $searchKey = $request->validated()["key"];

        $main = ItemProfile::search("$searchKey")
            ->with(['uomName:id,name,symbol,conversion', 'thicknessUom', 'lengthUom', 'widthUom', 'heightUom', 'volumeUom', 'outsideDiameterUom', 'insideDiameterUom'])
            ->limit(25)
            ->orderBy('item_description')
            ->get();

        return response()->json([
            'message' => "Successfully fetched.",
            'success' => true,
            'data' => SearchedItemsResource::collection($main)
        ]);

    }
}
