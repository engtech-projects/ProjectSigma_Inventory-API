<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestProcurementDetailedResource extends JsonResource
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
            'requisition_slip' => $this->requestStock
                ? new RequisitionSlipDetailedResource($this->requestStock)
                : null,
            'status' => $this->serve_status,
            'canvasser' => new CanvasserResource($this->canvasser),
            "price_quotations" => PriceQuotationListingResource::collection($this->priceQuotations),
            "price_quotation_count" => $this->priceQuotations->count(),

        ];
    }
}
