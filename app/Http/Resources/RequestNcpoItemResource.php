<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestNcpoItemResource extends JsonResource
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
            'changed_supplier_id' => $this->changed_supplier_id,
            'changed_item_description' => $this->changed_item_description,
            'changed_specification' => $this->changed_specification,
            'original_qty' => $this->canvassSummaryItem?->requisitionSlipItem?->quantity,
            'original_unit_price' => $this->canvassSummaryItem?->unit_price,
            'changed_qty' => $this->changed_qty,
            'changed_uom_id' => $this->changed_uom_id,
            'changed_unit_price' => $this->changed_unit_price,
            'changed_brand' => $this->changed_brand,
            'cancel_item' => $this->cancel_item,
            'new_total' => $this->new_total,
        ];
    }
}
