<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequisitionSlipDetailedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "request_for" => $this->request_for,
            "office_project" =>  $this->department->department_name ?? null,
            "address" => $this->section_address ?? "",
            "reference_no" => $this->reference_no ?? "",
            "date_prepared" => $this->date_prepared_human ?? "",
            "date_needed" => $this->date_needed_human ?? "",
            "equipment_no" => $this->equipment_no ?? "",
            "type_of_request" => $this->type_of_request ?? "",
            "contact_number" => $this->contact_no ?? "",
            "remarks" => $this->remarks ?? "",
            "current_smr" => $this->current_smr ?? "",
            "previous_smr" => $this->previous_smr ?? "",
            "unused_smr" => $this->unused_smr ?? "",
            "next_smr" => $this->next_smr ?? "",
            "price_quotations" => PriceQuotationListingResource::collection($this->priceQuotations),
            "price_quotation_count" => $this->priceQuotations->count(),
            "approvals" => new ApprovalAttributeResource(["approvals" => $this->approvals]),
            "next_approval" => $this->getNextPendingApproval(),
        ];
    }
}
