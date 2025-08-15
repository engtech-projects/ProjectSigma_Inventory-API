<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestCanvassSummaryDetailedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->createdAtDateHuman,
            'cs_number' => $this->cs_number,
            'terms_of_payment' => $this->terms_of_payment,
            'availability' => $this->availability,
            'delivery_terms' => $this->delivery_terms,
            'remarks' => $this->remarks,
            'price_quotation_id' => $this->priceQuotation->id,
            'supplier' => new RequestSupplierDetailedResource($this->priceQuotation->supplier),
            'items' => $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_id' => $item->item_id,
                    'unit_price' => $item->unit_price,
                ];
            }),


        ];
    }
}
