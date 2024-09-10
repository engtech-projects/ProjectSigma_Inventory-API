<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchUOM;
use App\Models\ItemGroup;
use App\Http\Requests\StoreItemGroupRequest;
use App\Http\Requests\UpdateItemGroupRequest;
use App\Http\Resources\ItemGroupResource;

class ItemGroupController extends Controller
{
    public function index()
    {
        $itemgroup = ItemGroup::paginate(10);
        $data = json_decode('{}');
        $data->message = "Successfully fetched.";
        $data->success = true;
        $data->data = $itemgroup;
        return response()->json($data);
    }

    public function search(SearchUOM $request)
    {
        $queryStr = $request->validated()['query'] ?? '';

        $query = ItemGroup::select('id', 'name', 'sub_groups')
            ->where('name', 'LIKE', "%{$queryStr}%")
            ->orWhere('sub_groups', 'LIKE', "%{$queryStr}%")
            ->get();

        return response()->json([
            'message' => 'Search Results',
            'success' => true,
            'data' => $query,
        ]);
    }
    public function get()
    {
        $main = ItemGroup::get();
        $data = json_decode('{}');
        $data->message = "Successfully fetched.";
        $data->success = true;
        $data->data = $main;
        return response()->json($data);
    }
    public function store(StoreItemGroupRequest $request, ItemGroup $resource)
    {
        $saved = $resource->create($request->validated());
        return response()->json([
            'message' => $saved ? 'Item Group Successfully created.' : 'Failed to create Item Group.',
            'success' => (bool) $saved,
            'data' => $saved ?? null,
            'data' => $saved ? new ItemGroupResource($saved) : null,
        ]);
    }

    public function show(ItemGroup $resource)
    {
        return response()->json([
            "message" => "Successfully fetched.",
            "success" => true,
            "data" => $resource
        ]);

    }

    public function update(UpdateItemGroupRequest $request, ItemGroup $resource)
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
    public function destroy(ItemGroup $resource)
    {
        if (!$resource) {
            return response()->json([
                'message' => 'Item Group not found.',
                'success' => false,
                'data' => null
            ], 404);
        }

        $deleted = $resource->delete();

        $response = [
            'message' => $deleted ? 'Item Group successfully deleted.' : 'Failed to delete Item Group.',
            'success' => $deleted,
            'data' => $resource
        ];

        return response()->json($response, $deleted ? 200 : 400);
    }


}
