<?php

namespace App\Http\Controllers;

use App\Http\Resources\MaterialsReceivingResource;
use App\Http\Resources\MaterialsReceivingResourceList;
use App\Http\Services\MaterialsReceivingService;
use App\Models\MaterialsReceiving;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaterialsReceivingController extends Controller
{
    protected $materialsReceivingService;
    public function __construct(MaterialsReceivingService $materialsReceivingService)
    {
        $this->materialsReceivingService = $materialsReceivingService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $main = MaterialsReceiving::with(['warehouse', 'supplier', 'project', 'items'])->paginate(10);
        $collection = MaterialsReceivingResource::collection($main)->response()->getData(true);

        return new JsonResponse([
            "message" => "Materials Receiving Successfully Fetched.",
            "success" => true,
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
        $resource->load('items', 'warehouse', 'supplier', 'project');
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

    public function allRequests()
    {
        $myRequest = $this->materialsReceivingService->getAllRequest();

        if ($myRequest->isEmpty()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No data found.',
            ], JsonResponse::HTTP_OK);
        }

        $requestResources = MaterialsReceivingResourceList::collection($myRequest)->response()->getData(true);

        return new JsonResponse([
            'message' => 'All Request Fetched.',
            'success' => true,
            'data' => $requestResources
        ]);
    }
}
