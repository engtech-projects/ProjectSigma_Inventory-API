<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchUOM;
use App\Models\ItemGroup;
use App\Http\Requests\StoreItemGroupRequest;
use App\Http\Requests\UpdateItemGroupRequest;

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
    public function store(StoreItemGroupRequest $request)
    {
        $itemgroup = new ItemGroup();
        $itemgroup->fill($request->validated());
        $data = json_decode('{}');
        if (!$itemgroup->save()) {
            $data->message = "Save failed.";
            $data->success = false;
            return response()->json($data, 400);
        }
        $data->message = "Successfully save.";
        $data->success = true;
        $data->data = $itemgroup;
        return response()->json($data, 201);
    }

    public function show(ItemGroup $resource)
    {
        return response()->json([
            "message" => "Successfully fetched.",
            "success" => true,
            "data" => $resource
        ]);

    }

    public function update(UpdateItemGroupRequest $request, $id)
    {
        $itemgroup = ItemGroup::find($id);
        $data = json_decode('{}');
        if (!is_null($itemgroup)) {
            $itemgroup->fill($request->validated());
            if ($itemgroup->save()) {
                $data->message = "Successfully updated.";
                $data->success = true;
                $data->data = $itemgroup;
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
        $itemgroup = ItemGroup::find($id);
        $data = json_decode('{}');
        if (!is_null($itemgroup)) {
            if ($itemgroup->delete()) {
                $data->message = "Successfully deleted.";
                $data->success = true;
                $data->data = $itemgroup;
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
