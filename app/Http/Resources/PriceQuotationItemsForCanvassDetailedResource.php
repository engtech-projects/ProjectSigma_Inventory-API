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
            'qty' => $this->request_stock_item?->quantity ?? 0,
            'unit' => $this->request_stock_item?->uom_name,
            'preferred_brand' => $this->request_stock_item?->preferred_brand,
            'actual_brand' => $this->actual_brand,
            'unit_price' => number_format($this->unit_price ?? 0, 2),
            'remarks_during_canvass' => $this->remarks_during_canvass,
            'total_amount' => number_format($this->total_amount ?? 0, 2),
            'is_quoted' => $this->is_quoted,
        ];
    }
}
