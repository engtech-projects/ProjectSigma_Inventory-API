<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ItemGroup;
use App\Http\Requests\StoreItemGroupRequest;
use App\Http\Requests\UpdateItemGroupRequest;

class ItemGroupController extends Controller
{
    public function index()
    {
        $itemgroup = ItemGroup::paginate(15);
        $data = json_decode('{}');
        $data->message = "Successfully fetched.";
        $data->success = true;
        $data->data = $itemgroup;
        return response()->json($data);
    }

    public function search(Request $request)
    {
        $queryStr = $request->validate([
            'query' => 'present|nullable|string|max:255',
        ])['query'];

        $query = ItemGroup::select('id', 'name', 'sub_groups')
            ->whereRaw('LOWER(name) LIKE ?', ["%".strtolower($queryStr)."%"])
            ->orWhereRaw('LOWER(sub_groups) LIKE ?', ["%".strtolower($queryStr)."%"])
            ->get();

        return response()->json([
            'message' => 'Search results',
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
    public function show($id)
    {
        $itemgroup = ItemGroup::find($id);
        $data = json_decode('{}');
        if (!is_null($itemgroup)) {
            $data->message = "Successfully fetched.";
            $data->success = true;
            $data->data = $itemgroup;
            return response()->json($data);
        }
        $data->message = "No data found.";
        $data->success = false;
        return response()->json($data, 404);
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
