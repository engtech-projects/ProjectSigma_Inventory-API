<?php

namespace App\Http\Controllers;

use App\Models\RequestProcurement;
use App\Http\Resources\RequestProcurementDetailedResource;
use App\Http\Resources\RequestProcurementListingResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class RequestProcurementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $procurements = RequestProcurement::with('requestStock')->paginate(10);
        return RequestProcurementListingResource::collection($procurements)
            ->additional([
                'success' => true,
                'message' => 'Request procurements retrieved successfully.',
            ]);
    }
    /**
     * Display the specified resource.
     */
    public function show(RequestProcurement $requestProcurement)
    {
        $procurement = $requestProcurement->with(['requestStock.department', 'canvassers'])->paginate(10);
        return RequestProcurementDetailedResource::collection($procurement)
            ->additional([
                'success' => true,
                'message' => 'Request procurement retrieved successfully.',
            ]);
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
