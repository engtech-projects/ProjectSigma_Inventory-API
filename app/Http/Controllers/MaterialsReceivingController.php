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
     * Display the specified resource.
     */
    public function getMaterialsReceivingByWarehouse($warehouse_id)
    {
        $main = MaterialsReceiving::with(['items', 'supplier', 'project'])
            ->where('warehouse_id', $warehouse_id)
            ->paginate(10);

        $collection = MaterialsReceivingResource::collection($main)->response()->getData(true);

        if ($collection['data']) {
            return response()->json([
                "message" => "Materials Receiving Successfully Fetched.",
                "success" => true,
                "data" => $collection['data']
            ]);
        } else {
            return response()->json([
                "message" => "No data found.",
                "success" => false,
                "data" => []
            ]);
        }
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
    public function destroy(MaterialsReceiving $resource)
    {
        if (!$resource) {
            return response()->json([
                'message' => 'Materials Receiving not found.',
                'success' => false,
                'data' => null
            ], 404);
        }

        $deleted = $resource->delete();

        $response = [
            'message' => $deleted ? 'Materials Receiving successfully deleted.' : 'Failed to delete a Materials Receiving.',
            'success' => $deleted,
            'data' => $resource
        ];

        return response()->json($response, $deleted ? 200 : 400);
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

    public function myApprovals()
    {
        $myApproval = $this->materialsReceivingService->getMyApprovals();

        if ($myApproval->isEmpty()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No data found.',
            ], JsonResponse::HTTP_OK);
        }

        $requestResources = MaterialsReceivingResourceList::collection($myApproval)->response()->getData(true);

        return new JsonResponse([
            'message' => 'My Approvals Fetched.',
            'success' => true,
            'data' => $requestResources
        ]);
    }
}
