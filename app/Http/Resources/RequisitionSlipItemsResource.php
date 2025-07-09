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
            // parent::toArray($request),
            'id' => $this->id,
            'item_id' => $this->id,
            'request_stock_id' => $this->request_stock_id,
            'item_description' => $this->item_description,
            'quantity' => $this->quantity,
            'uom' => $this->uomName,
            'specification' => $this->specification,
            'preferred_brand' => $this->preferred_brand,
            'reason' => $this->reason,
            'location' => $this->location,
            'location_qty' => $this->location_qty,
            'is_approved' => $this->is_approved,
        ];
    }
}
