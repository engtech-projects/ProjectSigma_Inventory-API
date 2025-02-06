<?php

namespace App\Http\Controllers;

use App\Http\Resources\MaterialsReceivingResource;
use App\Models\MaterialsReceiving;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaterialsReceivingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $main = MaterialsReceiving::with(['warehouse', 'supplier', 'project', 'items'])->paginate(10);
        $collection = MaterialsReceivingResource::collection($main)->response()->getData(true);

        return new JsonResponse([
            "success" => true,
            "message" => "Materials Receiving Successfully Fetched.",
            "data" => $collection,
        ], JsonResponse::HTTP_OK);
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
    public function show(MaterialsReceiving $resource)
    {
        $resource->load('items');
        return response()->json([
            "message" => "Successfully fetched.",
            "success" => true,
            "data" => new MaterialsReceivingResource($resource)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MaterialsReceiving $materialsReceiving)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MaterialsReceiving $materialsReceiving)
    {
        //
    }
}
