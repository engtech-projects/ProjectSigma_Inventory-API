<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaterialReceivingItemDetailedResource extends JsonResource
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
            'item_id' => $this->item_id,
            'item_code' => $this->item_code,
            'item_description' => $this->item_description,
            'specification' => $this->specification,
            'actual_brand_purchase' => $this->actual_brand_purchase ?? "",
            'requested_quantity' => $this->requested_quantity,
            'quantity' => $this->quantity,
            'uom_id' => $this->uom_id,
            'uom_name' => $this->uom->name,
            'unit_price' => $this->unit_price,
            'remarks' => $this->remarks,
            'acceptance_status' => $this->acceptance_status,
            'serve_status' => $this->serve_status,
            'processed' => $this->is_processed,
        ];
    }
}
