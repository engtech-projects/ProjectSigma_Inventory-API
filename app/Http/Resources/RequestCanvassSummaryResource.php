<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestCanvassSummaryResource extends JsonResource
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
            'price_quotation' => $this->priceQuotation,
            'items' => $this->items,
            'metadata' => $this->metadata,
            'approvals' => $this->approvals,
            'request_status' => $this->request_status,
            'created_by' => $this->created_by,
        ];
    }
}
