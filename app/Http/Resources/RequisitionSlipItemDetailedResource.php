<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequisitionSlipItemDetailedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "quantity " => $this->quantity,
            "unit_uom" => $this->uomName,
            "item_description" => $this->item_description,
            "specification" => $this->specification,
            "preferred_brand" => $this->preferred_brand,
            "date_prepared" => $this->date_prepared,
            "reason_for_request" => $this->reason_for_request,
            "location" => $this->location,
            "location_qty" => $this->location_qty,
            "status" => $this->status,
        ];
    }
}
