<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchItemProfile;
use App\Models\ItemProfile;
use App\Http\Requests\StoreItemProfileRequest;
use App\Http\Requests\UpdateItemProfileRequest;
use App\Http\Resources\ItemProfileResource;
use App\Http\Resources\SearchedItemsResource;
use App\Http\Resources\SyncItemProfilesResource;
use Illuminate\Http\JsonResponse;

class ItemProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $main = ItemProfile::paginate(10);
        $collection = ItemProfileResource::collection($main)->response()->getData(true);

        return new JsonResponse([
            "success" => true,
            "message" => "Request Item Profiling Successfully Fetched.",
            "data" => $collection,
        ], JsonResponse::HTTP_OK);
    }

    public function get()
    {
        $fetch = ItemProfile::isApproved()->get();
        if ($fetch->isEmpty()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No data found.',
            ], JsonResponse::HTTP_OK);
        }
        $requestResources = SyncItemProfilesResource::collection($fetch);
        return new JsonResponse([
            'success' => true,
            'message' => 'Suppliers Successfully Fetched.',
            'data' => $requestResources
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

        $searchKey = $request->validated()['query'] ?? '';

        $query = ItemProfile::IsApproved()
            ->where('active_status', 'Active')
            ->where(function ($q) use ($searchKey) {
                $q->where('item_description', 'LIKE', "%{$searchKey}%")
                ->orWhere('thickness', 'LIKE', "%{$searchKey}%")
                ->orWhere('length', 'LIKE', "%{$searchKey}%")
                ->orWhere('width', 'LIKE', "%{$searchKey}%")
                ->orWhere('height', 'LIKE', "%{$searchKey}%")
                ->orWhere('outside_diameter', 'LIKE', "%{$searchKey}%")
                ->orWhere('inside_diameter', 'LIKE', "%{$searchKey}%")
                ->orWhere('angle', 'LIKE', "%{$searchKey}%")
                ->orWhere('size', 'LIKE', "%{$searchKey}%")
                ->orWhere('weight', 'LIKE', "%{$searchKey}%")
                ->orWhere('volts', 'LIKE', "%{$searchKey}%")
                ->orWhere('plates', 'LIKE', "%{$searchKey}%")
                ->orWhere('part_number', 'LIKE', "%{$searchKey}%")
                ->orWhere('specification', 'LIKE', "%{$searchKey}%")
                ->orWhere('volume', 'LIKE', "%{$searchKey}%")
                ->orWhere('grade', 'LIKE', "%{$searchKey}%")
                ->orWhere('color', 'LIKE', "%{$searchKey}%");
            })
            ->limit(25)
            ->orderBy('item_description', 'desc')
            ->get();

        return response()->json([
            'message' => 'Successfully fetched.',
            'success' => true,
            'data' => SearchedItemsResource::collection($query)
        ]);
    }

}
