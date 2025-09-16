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
        $requisitionItem = $this->requisitionSlipItem;
        return [
            'item_id'        => $this->item_id,
            'quantity'       => $this->quantity ?? 0,
            'item_description' => $this->itemProfile?->item_description,
            'specification'  => $requisitionItem?->specification,
            'unit'           => $requisitionItem?->uom_name,
            'unit_price'     => $this->unit_price ?? 0,
            'total_amount'   => $this->total_amount ?? 0,
        ];
    }
}
