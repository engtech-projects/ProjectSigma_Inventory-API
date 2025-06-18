<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttachUsersProcurementRequest;
use App\Models\RequestProcurementCanvasser;
use App\Http\Requests\StoreRequestProcurementCanvasserRequest;
use App\Http\Requests\UpdateRequestProcurementCanvasserRequest;
use App\Models\RequestProcurement;
use Illuminate\Http\JsonResponse;

class RequestProcurementCanvasserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequestProcurementCanvasserRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(RequestProcurementCanvasser $requestProcurementCanvasser)
    {
        //
    }

    public function setCanvasser(AttachUsersProcurementRequest $request, RequestProcurement $requestProcurement)
    {
        $validated = $request->validated();
        try {
            $requestProcurement->canvassers()->attach($validated['user_ids']);
            return new JsonResponse([
                'success' => true,
                'message' => 'Users successfully attached as canvassers.',
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Attachment failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RequestProcurementCanvasser $requestProcurementCanvasser)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequestProcurementCanvasserRequest $request, RequestProcurementCanvasser $requestProcurementCanvasser)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RequestProcurementCanvasser $requestProcurementCanvasser)
    {
        //
    }
}
