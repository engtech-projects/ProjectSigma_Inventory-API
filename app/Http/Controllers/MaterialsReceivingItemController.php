<?php

namespace App\Http\Controllers;

use App\Models\MaterialsReceivingItem;
use Illuminate\Http\Request;

class MaterialsReceivingItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(MaterialsReceivingItem $materialsReceivingItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MaterialsReceivingItem $materialsReceivingItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MaterialsReceivingItem $materialsReceivingItem)
    {
        //
    }

    public function accept(MaterialsReceivingItem $resource)
    {
        if ($resource->status === 'Accepted') {
            return response()->json([
                'message' => "". $resource->item_profile_data['item_description'] . " has already been accepted.",
                'data' => $resource
            ], 200);
        }
        $resource->status = 'Accepted';
        $resource->save();

        return response()->json([
            'message' => "". $resource->item_profile_data['item_description'] . " has been successfully accepted.",
            'data' => $resource
        ]);
    }

    public function reject(MaterialsReceivingItem $resource)
    {
        if ($resource->status === 'Rejected') {
            return response()->json([
                'message' => "". $resource->item_profile_data['item_description'] . " has already been rejected.",
                'data' => $resource
            ], 200);
        }
        $resource->status = 'Rejected';
        $resource->save();

        return response()->json([
            'message' => "". $resource->item_profile_data['item_description'] . " has been sucessfully rejected.",
            'data' => $resource
        ]);
    }
}
