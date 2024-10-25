<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BOMDetailsResource extends JsonResource
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
            'item_id' => $this->item_id,
            'uom_id' => $this->uom_id,
            'unit_price' => $this->unit_price,
            'quantity' => $this->quantity,
        ];
    }
}
