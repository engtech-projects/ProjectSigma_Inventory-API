<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PriceQuotationItemsForCanvassDetailedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "item_id" => $this->item_id,
            'item_description' => $this->request_stock_item?->item_description,
            'specification' => $this->request_stock_item?->specification,
            'qty' => $this->request_stock_item?->quantity,
            'unit' => $this->request_stock_item?->uom_name,
            'preferred_brand' => $this->request_stock_item?->preferred_brand,
            'brand' => $this->actual_brand,
            'price' => $this->unit_price,
            'remarks' => $this->remarks_during_canvass,
            'total_amount' => $this->total_amount,
            'is_quoted' => $this->is_quoted,
        ];
    }
}
