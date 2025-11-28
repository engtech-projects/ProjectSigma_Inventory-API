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
            'requisition_slip' => $this->requisitionSlip
                ? new RequisitionSlipDetailedResource($this->requisitionSlip)
                : null,
            'status' => $this->serve_status,
            'canvasser' => new CanvasserResource($this->canvasser),
            "price_quotation_count" => $this->priceQuotations->count(),
            'price_quotations' => PriceQuotationListingResource::collection($this->whenLoaded('priceQuotations')),
            'canvass_summaries' => RequestCanvassSummaryListingResource::collection($this->whenLoaded('canvassSummaries')),
            'purchase_orders' => RequestPurchaseOrderListingResource::collection(
                $this->purchaseOrders
            ),
            'ncpo' => RequestNcpoListingResource::collection(
                $this->ncpo
            ),
        ];
    }
}
