<?php

namespace App\Http\Controllers;

use App\Enums\AccessibilityInventory;
use App\Enums\UserTypes;
use App\Models\RequestProcurement;
use App\Http\Resources\RequestProcurementDetailedResource;
use App\Http\Resources\RequestProcurementListingResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

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
                'message' => 'Successfully fetched.',
            ]);
    }
    /**
     * Display the specified resource.
     */
    public function show(RequestProcurement $resource)
    {
        return new JsonResponse([
            'success' => true,
            'message' => 'Request procurement retrieved successfully.',
            'data' => new RequestProcurementDetailedResource($resource)
        ]);
    }

    public function unservedRequests()
    {
        $userId = auth()->id();
        $user = Auth::user();
        $userAccessibilitiesNames = $user->accessibilities_name;
        $isUserSetCanvasser = $this->checkUserAccessManual($userAccessibilitiesNames, [AccessibilityInventory::INVENTORY_PROCUREMENT_PROCUREMENTREQUESTS_SETCANVASSER->value]) || Auth::user()->type == UserTypes::ADMINISTRATOR->value;
        $procurements = RequestProcurement::with('requestStock')
        ->isUnserved()
        ->when($isUserSetCanvasser, function ($query) use ($userId) {
            return $query->isCanvasser($userId);
        })
        ->paginate(10);
        return RequestProcurementListingResource::collection($procurements)
        ->additional([
            'success' => true,
            'message' => 'Unserved request procurements fetched successfully.',
        ]);
    }
}
