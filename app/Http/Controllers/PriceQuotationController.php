<?php

namespace App\Http\Controllers;

use App\Models\PriceQuotation;
use App\Http\Requests\StorePriceQuotationRequest;
use App\Http\Resources\PriceQuotationDetailedResource;
use App\Models\PriceQuotationItem;
use App\Models\RequestProcurement;
use App\Models\RequestSupplier;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Http\Request;

class PriceQuotationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePriceQuotationRequest $request, RequestProcurement $pr_id)
    {
        $validated = $request->validated();

        $supplier = RequestSupplier::findOrFail($validated['supplier_id']);
        if (!$supplier->isApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'Supplier is not approved.',
            ], 422);
        }
        $priceQuotation = $pr_id->priceQuotations()->create([
            'supplier_id' => $supplier->id,
        ]);

        foreach ($validated['items'] as $item) {
            $priceQuotation->items()->create([
                'item_id' => $item['item_id'],
                'actual_brand' => $item['actual_brand'] ?? null,
                'unit_price' => $item['unit_price'] ?? null,
                'remarks_during_canvass' => $item['remarks_during_canvass'] ?? null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Price quotation created successfully.',
            'data' => $priceQuotation
        ], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(PriceQuotation $pq_id)
    {
        $pq_id->load([
            'supplier',
            'items.requestStockItem.itemProfile',
            'items.requestStockItem.uom',
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Price quotation retrieved successfully.',
            'data' => new PriceQuotationDetailedResource($pq_id)
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PriceQuotationItem $priceQuotationItem)
    {
        //
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PriceQuotation $priceQuotation)
    {
        //
    }
}
