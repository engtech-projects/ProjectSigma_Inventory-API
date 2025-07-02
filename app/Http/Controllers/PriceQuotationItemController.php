<?php

namespace App\Http\Controllers;

use App\Models\PriceQuotationItem;
use App\Http\Requests\StorePriceQuotationItemRequest;
use App\Http\Requests\UpdatePriceQuotationItemRequest;

class PriceQuotationItemController extends Controller
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
    public function store(StorePriceQuotationItemRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(PriceQuotationItem $priceQuotationItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePriceQuotationItemRequest $request, PriceQuotationItem $priceQuotationItem)
    {
        $priceQuotationItem->fill($request->validated());
        if ($priceQuotationItem->save()) {
            return response()->json([
                "message" => "Price quotation item updated successfully.",
                "success" => true,
                "data" => $priceQuotationItem->refresh()
            ]);
        }

        return response()->json([
            'message' => "Failed to update.",
            'success' => false,
            'data' => $priceQuotationItem
        ], 400);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PriceQuotationItem $priceQuotationItem)
    {
        //
    }
}
