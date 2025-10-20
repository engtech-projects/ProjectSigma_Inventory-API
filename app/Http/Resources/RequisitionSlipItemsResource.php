<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequisitionSlipItemsResource extends JsonResource
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
            "item_id" => $this->item_id,
            "request_requisition_slip_id" => $this->request_requisition_slip_id,
            "quantity" => $this->quantity,
            "unit" => $this->unit,
            "specification" => $this->specification,
            "preferred_brand" => $this->preferred_brand,
            "reason" => $this->reason,
            "location" => $this->location,
            "location_qty" => $this->location_qty,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "deleted_at" => $this->deleted_at,
            "item_description" => $this->item_description,
            "uom" => $this->uom_name,
            "is_approved" => $this->is_approved,
            "processing_details" => $this->processing_details,
        ];
    }
}
