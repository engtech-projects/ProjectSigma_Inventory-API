<?php

namespace App\Http\Controllers;

use App\Models\PriceQuotation;
use App\Http\Requests\StorePriceQuotationRequest;
use App\Http\Resources\PriceQuotationDetailedResource;
use App\Models\RequestProcurement;
use App\Models\RequestSupplier;
use App\Traits\HasReferenceNumber;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PriceQuotationController extends Controller
{
    use HasReferenceNumber;

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePriceQuotationRequest $request, RequestProcurement $requestProcurement)
    {
        $validated = $request->validated();
        $items = $request->input('items');

        if (!RequestSupplier::whereKey($validated['supplier_id'])->isApproved()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Supplier is not approved.',
            ], 422);
        }

        try {
            $quotation = DB::transaction(function () use ($validated, $requestProcurement, $items, $request) {

                $quotationNo = PriceQuotation::generateReferenceNumber(
                    'metadata->quotation_no',
                    fn ($prefix, $datePart, $number) => "{$prefix}-{$datePart}-{$number}",
                    ['prefix' => 'RPQ', 'dateFormat' => 'Y-m']
                );

                $metadata = array_merge(
                    Arr::only($request->all(), ['date', 'address', 'contact_person', 'contact_no', 'conso_reference_no']),
                    ['quotation_no' => $quotationNo]
                );

                $quotation = $requestProcurement->priceQuotations()->create([
                    'supplier_id' => $validated['supplier_id'],
                    'metadata' => $metadata,
                ]);


                $quotation->items()->createMany(
                    array_map(fn ($item) => [
                        'item_id' => $item['item_id'],
                        'actual_brand' => $item['actual_brand'] ?? null,
                        'unit_price' => $item['unit_price'] ?? null,
                        'remarks_during_canvass' => $item['remarks_during_canvass'] ?? null,
                        'metadata' => (object) array_filter(Arr::only($item, [
                            'item_description',
                            'specification',
                            'quantity',
                            'uom',
                            'preferred_brand',
                        ]), fn ($value) => !is_null($value)),
                    ], $items)
                );

                return $quotation;
            });

            return response()->json([
                'success' => true,
                'message' => 'Price quotation created successfully.',
                'data' => $quotation
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to store price quotation.',
            ], 500);
        }
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
