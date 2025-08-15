<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemDetailedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id"=> $this->id,
            'item_id' => $this->item_id,
            'unit_price' => $this->unit_price,
            'total_amount' => optional($this->requestCanvassSummary->priceQuotation)->grand_total_amount ?? $this->requestCanvassSummary->sum('unit_price'),
        ];
    }
}
