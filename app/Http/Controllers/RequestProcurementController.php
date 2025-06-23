<?php

namespace App\Http\Controllers;

use App\Models\RequestProcurement;
use App\Http\Resources\RequestProcurementDetailedResource;
use App\Http\Resources\RequestProcurementListingResource;
use App\Utils\PaginateResourceCollection;
use Illuminate\Http\JsonResponse;

class RequestProcurementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $procurements = RequestProcurement::with('requestStock')->get();
        $dataReturn = collect(RequestProcurementListingResource::collection($procurements));
        return new JsonResponse([
            "success" => true,
            "message" => "Successfully fetched.",
            "data" => PaginateResourceCollection::paginate($dataReturn, 10)
        ], JsonResponse::HTTP_OK);
    }
    /**
     * Display the specified resource.
     */
    public function show(RequestProcurement $resource)
    {
        return new JsonResponse([
            'success' => true,
            'message' => 'Request procurement retrieved successfully.',
            'data' => new RequestProcurementDetailedResource(
                $resource->load(['requestStock.department', 'canvassers'])
            )
        ], JsonResponse::HTTP_OK);
    }

    public function unservedRequests()
    {
        $userId = auth()->id();
        $procurements = RequestProcurement::with('requestStock')
            ->isUnserved()
            ->isCanvasser($userId)
            ->paginate(10);

        return RequestProcurementListingResource::collection($procurements)
            ->additional([
                'success' => true,
                'message' => 'Unserved request procurements fetched successfully.',
            ]);
    }
}
