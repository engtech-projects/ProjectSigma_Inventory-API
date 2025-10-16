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
            "id" => $this->id,
            "item_id" => $this->item_id,
            'item_description' => $this->request_stock_item?->item_description,
            'specification' => $this->request_stock_item?->specification,
            'qty' => $this->request_stock_item?->quantity,
            'unit' => $this->request_stock_item?->uom_name,
            'preferred_brand' => $this->request_stock_item?->preferred_brand,
            'actual_brand' => $this->actual_brand,
            'unit_price' => $this->unit_price,
            'remarks_during_canvass' => $this->remarks_during_canvass,
            'total_amount' => $this->total_amount,
        ];
    }
}
