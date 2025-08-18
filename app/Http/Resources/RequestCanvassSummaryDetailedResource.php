<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestCanvassSummaryDetailedResource extends JsonResource
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
            'project_code' => optional($this->priceQuotation->requestProcurement->requisitionSlip)->project_department_name,
            'reference_number' => optional($this->priceQuotation->requestProcurement->requisitionSlip)->reference_no,
            'date' => $this->createdAtDateHuman,
            'cs_number' => $this->cs_number,
            'terms_of_payment' => $this->terms_of_payment,
            'availability' => $this->availability,
            'delivery_terms' => $this->delivery_terms,
            'remarks' => $this->remarks,
            'price_quotation_id' => $this->priceQuotation->id,
            'supplier' => new RequestSupplierDetailedResource($this->priceQuotation->supplier),
            'items' => CanvassSummaryItemDetailedResource::collection($this->items),
            'grand_total_amount' => $this->grand_total_amount,
            "approvals" => new ApprovalAttributeResource(["approvals" => $this->approvals]),
            "next_approval" => $this->getNextPendingApproval(),
        ];
    }
}
