<?php

namespace App\Http\Controllers;

use App\Http\Resources\RequestNcpoDetailedResource;
use App\Http\Resources\RequestNcpoListingResource;
use App\Models\RequestNcpo;

class RequestNcpoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $requestNCPOs = RequestNcpo::paginate(config('app.pagination.per_page', 15));
        return RequestNcpoListingResource::collection($requestNCPOs)
        ->additional([
            'message' => 'Request NCPOs retrieved successfully.',
            'success' => true,
        ]);
    }
    /**
     * Display the specified resource.
     */
    public function show(RequestNcpo $resource)
    {
        $resource->load([
            'items.item',
            'purchaseOrder',
        ]);
        return RequestNcpoDetailedResource::make($resource)
            ->additional([
                'message' => 'Request NCPO retrieved successfully.',
                'success' => true,
            ]);
    }
}
