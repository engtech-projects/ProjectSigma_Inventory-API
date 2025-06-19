<?php

namespace App\Http\Controllers;

use App\Models\RequestProcurement;
use App\Http\Resources\RequestProcurementDetailedResource;
use App\Http\Resources\RequestProcurementListingResource;
use Illuminate\Http\JsonResponse;

class RequestProcurementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $procurements = RequestProcurement::with('requestStock')->paginate(10);

        $returnData = RequestProcurementListingResource::collection($procurements);

        return new JsonResponse([
            'success' => true,
            'message' => 'Unserved request procurements fetched successfully',
            'data' => $returnData
        ]);
    }
    /**
     * Display the specified resource.
     */
    public function show(RequestProcurement $requestProcurement)
    {
        $procurement = $requestProcurement->with(['requestStock.department', 'canvassers'])->paginate(10);

        $returnData = RequestProcurementDetailedResource::collection($procurement);

        return new JsonResponse([
            'success' => true,
            'message' => 'Unserved request procurements fetched successfully',
            'data' => $returnData
        ]);
    }

    public function unservedRequests(RequestProcurement $requestProcurement)
    {
        $userId = auth()->id();
        $procurements = RequestProcurement::with('requestStock')
            ->isUnserved()
            ->isCanvasser($userId)
            ->paginate(10);

        $returnData = RequestProcurementListingResource::collection($procurements);

        return new JsonResponse([
            'success' => true,
            'message' => 'Unserved request procurements fetched successfully',
            'data' => $returnData
        ]);
    }
}
