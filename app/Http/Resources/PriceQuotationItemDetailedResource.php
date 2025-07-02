<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PriceQuotationItemDetailedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'item_description' => $this->requestStockItem?->item_description,
            'specification' => $this->requestStockItem?->specification,
            'qty' => $this->requestStockItem?->quantity,
            'unit' => $this->requestStockItem?->uom_name,
            'preferred_brand' => $this->requestStockItem?->preferred_brand,
            'brand' => $this->actual_brand,
            'price' => $this->unit_price,
            'remarks' => $this->remarks_during_canvass,
        ];
    }
}
