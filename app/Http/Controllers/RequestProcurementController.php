<?php

namespace App\Http\Controllers;

use App\Enums\AccessibilityInventory;
use App\Enums\UserTypes;
use App\Models\RequestProcurement;
use App\Http\Resources\RequestProcurementDetailedResource;
use App\Http\Resources\RequestProcurementListingResource;
use App\Http\Traits\CheckAccessibility;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class RequestProcurementController extends Controller
{
    use CheckAccessibility;
    public function index()
    {
        $procurements = RequestProcurement::with('requisitionSlip')->paginate(10);
        return RequestProcurementListingResource::collection($procurements)
            ->additional([
                'success' => true,
                'message' => 'Successfully fetched.',
            ]);
    }
    public function show(RequestProcurement $resource)
    {
        $resource->load([
            'priceQuotations' => [
                'canvassSummaries.purchaseOrder' => function ($query) {
                    $query->latest();
                },
            ],
            'canvasser',
            'canvassSummaries' => function ($query) {
                $query->latest();
            },
            'priceQuotations.canvassSummaries.purchaseOrder',
        ]);
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
        $procurements = RequestProcurement::with('requisitionSlip')
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
