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
    public function show($id)
    {
        $procurement = RequestProcurement::with(['requestStock.department', 'canvassers'])
        ->find($id);

        if (!$procurement) {
            return response()->json([
                'success' => false,
                'message' => 'No data found.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Request procurement retrieved successfully.',
            'data' => new RequestProcurementDetailedResource($procurement)
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
