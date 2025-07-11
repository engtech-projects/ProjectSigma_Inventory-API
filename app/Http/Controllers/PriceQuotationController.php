<?php

namespace App\Http\Controllers;

use App\Models\PriceQuotation;
use App\Http\Requests\StorePriceQuotationRequest;
use App\Http\Resources\PriceQuotationDetailedResource;
use App\Models\RequestProcurement;
use App\Traits\HasReferenceNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PriceQuotationController extends Controller
{
    use HasReferenceNumber;

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePriceQuotationRequest $request, RequestProcurement $requestProcurement)
    {
        $validated = $request->validated();
        // Log::info(
        //     'validated: ',
        //     array_map(function ($value) {
        //         return is_array($value) ? json_encode($value, JSON_PRETTY_PRINT) : $value;
        //     }, $validated)
        // );
        $quotation = DB::transaction(function () use ($validated, $requestProcurement, $request) {
            $quotationNo = PriceQuotation::generateReferenceNumber(
                'metadata->quotation_no',
                fn ($prefix, $datePart, $number) => "{$prefix}-{$datePart}-{$number}",
                ['prefix' => 'RPQ', 'dateFormat' => 'Y-m']
            );

            $metadata = [
                ...$request->only(['date', 'address', 'contact_person', 'contact_no', 'conso_reference_no']),
                'quotation_no' => $quotationNo,
            ];

            $quotation = $requestProcurement->priceQuotations()->create([
                'supplier_id' => $validated['supplier_id'],
                'metadata' => $metadata,
            ]);

            $quotation->items()->createMany($validated['items']);

            return $quotation;
        });

        if ($quotation) {
            return new JsonResponse([
                'success' => true,
                'message' => 'Price quotation created successfully.',
            ], JsonResponse::HTTP_OK);
        } else {
            return new JsonResponse([
                'success' => true,
                'message' => 'Price quotation creation failed.',
            ], JsonResponse::HTTP_OK);
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
