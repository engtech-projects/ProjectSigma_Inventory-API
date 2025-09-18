<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestSupplierResource extends JsonResource
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
            "supplier_code" => $this->supplier_code,
            "company_name" => $this->company_name,
            "company_address" => $this->company_address,
            "company_contact_number" => $this->company_contact_number,
            "company_email" => $this->company_email,
            "contact_person_name" => $this->contact_person_name,
            "contact_person_number" => $this->contact_person_number,
            "contact_person_designation" => $this->contact_person_designation,
            "type_of_ownership" => $this->type_of_ownership,
            "nature_of_business" => $this->nature_of_business,
            "products_services" => $this->products_services,
            "classification" => $this->classification,
            "tin" => $this->tin,
            "terms_and_conditions" => $this->terms_and_conditions,
            "filled_by" => $this->filled_by,
            "filled_designation" => $this->filled_designation,
            "filled_date" => $this->filled_date,
            "requirements_complete" => $this->requirements_complete,
            "remarks" => $this->remarks,
            "created_by" => $this->created_by,
            "uploads" => $this->uploads,
            "approvals" => new ApprovalAttributeResource(["approvals" => $this->approvals]),
            "next_approval" => $this->getNextPendingApproval(),
        ];
    }
}
