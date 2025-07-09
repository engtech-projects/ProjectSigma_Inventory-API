<?php

namespace App\Http\Controllers;

use App\Models\PriceQuotation;
use App\Http\Requests\StorePriceQuotationRequest;
use App\Http\Resources\PriceQuotationDetailedResource;
use App\Models\RequestProcurement;
use App\Models\RequestSupplier;
use App\Traits\HasReferenceNumber;
use Illuminate\Http\JsonResponse;
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

        $supplier = RequestSupplier::findOrFail($validated['supplier_id']);
        if (!$supplier->isApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'Supplier is not approved.',
            ], 422);
        }

        return DB::transaction(function () use ($validated, $requestProcurement, $supplier) {
            $quotationNo = PriceQuotation::generateReferenceNumber(
                'metadata->quotation_no',
                fn ($prefix, $datePart, $number) => "{$prefix}-{$datePart}-{$number}",
                ['prefix' => 'RPQ', 'dateFormat' => 'Y-m']
            );

            $metadata = Arr::only($validated, [
                'date', 'address', 'contact_person', 'contact_no', 'conso_reference_no',
            ]);
            $metadata['quotation_no'] = $quotationNo;

            $priceQuotation = $requestProcurement->priceQuotations()->create([
                'supplier_id' => $supplier->id,
                'metadata' => $metadata,
            ]);

            $itemsData = array_map(function ($item) {
                return [
                    'item_id' => $item['item_id'],
                    'actual_brand' => $item['actual_brand'] ?? null,
                    'unit_price' => $item['unit_price'] ?? null,
                    'remarks_during_canvass' => $item['remarks_during_canvass'] ?? null,
                    'metadata' => Arr::only($item, [
                        'item_description', 'specification', 'quantity', 'uom', 'preferred_brand'
                    ]),
                ];
            }, $validated['items']);

            $priceQuotation->items()->createMany($itemsData);

            return response()->json([
                'success' => true,
                'message' => 'Price quotation created successfully.',
                'data' => $priceQuotation
            ], 201);
        });
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
