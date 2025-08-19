<?php

namespace App\Http\Controllers;

use App\Models\PriceQuotation;
use App\Http\Requests\StorePriceQuotationRequest;
use App\Http\Resources\PriceQuotationDetailedResource;
use App\Http\Resources\PriceQuotationForCanvassResource;
use App\Models\PriceQuotationItem;
use App\Models\RequestProcurement;
use App\Traits\HasReferenceNumber;
use App\Traits\ModelHelpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PriceQuotationController extends Controller
{
    use HasReferenceNumber;
    use ModelHelpers;

    public function quotations(RequestProcurement $requestProcurement)
    {
        $priceQuotations = $requestProcurement->priceQuotations()
            ->with([
                'supplier',
                'items' => function ($query) {
                    $query->orderBy('id');
                },
                'requestProcurement.requisitionSlip.items.itemProfile',
            ])
            ->latest()
            ->get();
        $requisitionItems = $requestProcurement->requisitionSlip->items->keyBy('item_id');
        $priceQuotations->each(function ($quotation) use ($requisitionItems) {
            $quotationItems = $quotation->items->keyBy('item_id');
            $quotation->items = $requisitionItems->map(function ($reqItem) use ($quotationItems) {
                return $quotationItems->get($reqItem->item_id) ?? new PriceQuotationItem([
                    'item_id'    => $reqItem->item_id,
                    'unit_price' => 0,
                    'quantity'   => 0,
                ]);
            })->values();
        });
        return PriceQuotationForCanvassResource::collection($priceQuotations)->additional([
            "success" => true,
            "message" => "Price quotations for canvass successfully fetched.",
        ]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePriceQuotationRequest $request, RequestProcurement $requestProcurement)
    {
        $validated = $request->validated();
        $quotation = DB::transaction(function () use ($validated, $requestProcurement, $request) {
            $quotationNo = PriceQuotation::generateReferenceNumber(
                'quotation_no',
                fn ($prefix, $datePart, $number) => "{$prefix}-{$datePart}-{$number}",
                ['prefix' => 'RPQ', 'dateFormat' => 'Y-m']
            );
            $metadata = [
                ...$request->only(['date', 'address', 'contact_person', 'contact_no', 'conso_reference_no']),
            ];
            $quotation = $requestProcurement->priceQuotations()->create([
                'supplier_id' => $validated['supplier_id'],
                'metadata' => $metadata,
                'quotation_no' => $quotationNo,
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
                'success' => false,
                'message' => 'Price quotation creation failed.',
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PriceQuotation $priceQuotation)
    {
        $priceQuotation->load([
            'supplier',
            'items',
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Price quotation retrieved successfully',
            'data' => new PriceQuotationDetailedResource($priceQuotation)
        ]);
    }
}
