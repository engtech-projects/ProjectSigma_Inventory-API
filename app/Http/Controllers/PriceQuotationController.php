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
     * Store a newly created resource in storage.
     */
    public function store(StorePriceQuotationRequest $request, RequestProcurement $requestProcurement)
    {
        $validated = $request->validated();

        $supplier = RequestSupplier::findOrFail($validated['supplier_id']);
        if (!$supplier->isApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'Supplier is not approved.',
            ], 422);
        }
        $priceQuotation = $requestProcurement->priceQuotations()->create([
            'supplier_id' => $supplier->id,
        ]);

        $itemsData = collect($validated['items'])->map(function ($item) {
            return [
                'item_id' => $item['item_id'],
                'actual_brand' => $item['actual_brand'] ?? null,
                'unit_price' => $item['unit_price'] ?? null,
                'remarks_during_canvass' => $item['remarks_during_canvass'] ?? null,
            ];
        })->toArray();

        $priceQuotation->items()->createMany($itemsData);

        return response()->json([
            'success' => true,
            'message' => 'Price quotation created successfully.',
            'data' => $priceQuotation
        ], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(PriceQuotation $priceQuotation)
    {
        $priceQuotation->load([
            'supplier',
            'items.requestStockItem.itemProfile',
            'items.requestStockItem.uom',
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Price quotation retrieved successfully.',
            'data' => new PriceQuotationDetailedResource($priceQuotation)
        ]);
    }

}
