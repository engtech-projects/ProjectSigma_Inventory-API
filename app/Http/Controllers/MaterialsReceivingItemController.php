<?php

namespace App\Http\Controllers;

use App\Models\MaterialsReceivingItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class MaterialsReceivingItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $main = MaterialsReceivingItem::get();

        return new JsonResponse([
            "message" => "Materials Receiving Item Successfully Fetched.",
            "success" => true,
            "data" => $main,
        ], JsonResponse::HTTP_OK);
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
    public function show(MaterialsReceivingItem $resource)
    {
        return response()->json([
            "message" => "Successfully fetched.",
            "success" => true,
            "data" => $resource
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MaterialsReceivingItem $materialsReceivingItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MaterialsReceivingItem $materialsReceivingItem)
    {
        //
    }

    public function acceptAll(Request $request, MaterialsReceivingItem $resource)
    {
        if ($resource->status === 'Accepted') {
            $resource->update([
                'accepted_qty' => max($resource->qty, $resource->accepted_qty),
                'remarks' => 'Accepted',
            ]);

            return response()->json([
                'message' => "Accepted quantity and remark have been updated.",
                'data' => $resource
            ], 200);
        }

        $resource->update([
            'status' => 'Accepted',
            'remarks' => 'Accepted',
            'accepted_qty' => max($resource->qty, $resource->accepted_qty),
        ]);

        return response()->json([
            'message' => "Item has been successfully accepted.",
            'data' => $resource
        ]);
    }

    public function acceptWithDetails(Request $request, MaterialsReceivingItem $resource)
    {
        if ($resource->status === 'Accepted') {
            $remarks = $request->input('remarks');
            $accepted_qty = $request->input('accepted_qty');
            $resource->update([
                'accepted_qty' => $accepted_qty,
                'remarks' => $remarks
            ]);

            return response()->json([
                'message' => "Accepted quantity and remark has been updated.",
                'data' => $resource
            ], 200);
        }

        $remarks = $request->input('remarks');
        $accepted_qty = $request->input('accepted_qty');
        $resource->update([
            'status' => 'Accepted',
            'remarks' => $remarks,
            'accepted_qty' => $accepted_qty,
        ]);

        return response()->json([
            'message' => "Item has been successfully accepted.",
            'data' => $resource
        ]);
    }

    public function reject(Request $request, MaterialsReceivingItem $resource)
    {
        if ($resource->status === 'Rejected') {
            return response()->json([
                'message' => "Item has already been rejected.",
                'data' => $resource
            ], 200);
        }

        $remarks = $request->input('remarks');

        $resource->update([
            'status' => 'Rejected',
            'remarks' => $remarks,
        ]);

        return response()->json([
            'message' => "Item has been successfully rejected.",
            'data' => $resource
        ]);
    }
}
