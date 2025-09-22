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
            'changed_supplier_name' => $this->supplier?->company_name,
            'changed_item_description' => $this->changed_item_description,
            'changed_specification' => $this->changed_specification,
            'original_qty' => number_format($this->original_quantity, 2),
            'original_unit_price' => number_format($this->original_unit_price, 2),
            'changed_qty' => number_format($this->changed_qty, 2),
            'changed_uom_id' => $this->changed_uom_id,
            'changed_uom' => $this->requisitionSlip?->items?->uom_name,
            'changed_unit_price' => number_format($this->changed_unit_price, 2),
            'changed_brand' => $this->changed_brand,
            'cancel_item' => $this->cancel_item,
            'new_total' => number_format($this->new_total, 2),
        ];
    }
}
