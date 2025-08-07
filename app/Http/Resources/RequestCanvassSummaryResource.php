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
            'price_quotation_id' => $this->price_quotation_id,
            'price_quotation' => new PriceQuotationListingResource($this->whenLoaded('priceQuotation')),
            'items' => ItemProfileResource::collection($this->whenLoaded('items')),
            'metadata' => $this->metadata,
            'approvals' => $this->approvals,
            'request_status' => $this->request_status,
            'created_by' => $this->created_by,
        ];
    }
}
