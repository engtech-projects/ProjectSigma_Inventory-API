<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestBomDetailsDetailedResource extends JsonResource
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
            'request_bom_id' => $this->request_bom_id,
            'item_code' => $this->items->item_code,
            'item_id' => $this->item_id,
            'item_summary' => $this->item_summary,
            'uom_id' => $this->uom_id,
            'unit' => $this->uom->name,
            'price' => $this->unit_price,
            'quantity' => $this->quantity,
            'convertable_units' => $this->convertable_units,
            'amount' => number_format($this->unit_price * $this->quantity, 2),
        ];
    }
}
