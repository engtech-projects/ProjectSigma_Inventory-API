<?php

namespace App\Http\Controllers;

use App\Http\Resources\SyncItemProfilesResource;
use App\Http\Resources\SyncSuppliersResource;
use App\Http\Resources\SyncUOMResource;
use App\Models\ItemProfile;
use App\Models\RequestSupplier;
use App\Models\UOM;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiServiceController extends Controller
{
    public function getSuppliersList()
    {
        $fetch = RequestSupplier::isApproved()
            ->with(['uploads'])
            ->orderBy('created_at', 'DESC')
            ->get();
        if ($fetch->isEmpty()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No data found.',
            ], JsonResponse::HTTP_OK);
        }
        $requestResources = SyncSuppliersResource::collection($fetch);
        return new JsonResponse([
            'success' => true,
            'message' => 'Suppliers Successfully Fetched.',
            'data' => $requestResources
        ]);

    }
    public function getItemprofilesList()
    {
        $fetch = ItemProfile::where("is_approved", "1")->get();
        if ($fetch->isEmpty()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No data found.',
            ], JsonResponse::HTTP_OK);
        }
        $requestResources = SyncItemProfilesResource::collection($fetch);
        return new JsonResponse([
            'success' => true,
            'message' => 'Item Profiles Successfully Fetched.',
            'data' => $requestResources
        ]);

    }
    public function getUomsList()
    {

        $fetch = UOM::get();
        if ($fetch->isEmpty()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No data found.',
            ], JsonResponse::HTTP_OK);
        }
        $requestResources = SyncUOMResource::collection($fetch);
        return new JsonResponse([
            'success' => true,
            'message' => 'UOMs Successfully Fetched.',
            'data' => $requestResources
        ]);
    }
}
