<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CanvassSummaryItemDetailedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'item_id'        => $this->item_id,
            'quantity'       => $this->quantity,
            'item_description' => $this->itemProfile?->item_description,
            'specification'  => $requisitionItem?->specification,
            'unit'           => $requisitionItem?->uom_name,
            'unit_price'     => $this->unit_price,
            'total_amount'   => $this->total_amount,
            'test'   => 'test',
        ];
    }
}
