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
            'id'                 => $this->id,
            'item_id'            => $this->item_id,
            'quantity'           => (int) $this->quantity,
            'uom'                => $this->uom_name,
            'uom_id'             => $this->unit,
            'item_description'   => $this->item_description,
            'specification'      => $this->specification ?? '',
            'preferred_brand'    => $this->preferred_brand ?? '',
            'reason'             => $this->reason ?? '',
            // Stock Availability
            'has_stock'          => $this->is_available_in_any_warehouse,
            'total_available'    => (int) $this->total_available_in_warehouses,
            'available_in_warehouses' => $this->when(
                $this->is_available_in_any_warehouse,
                $this->available_in_warehouses
            ),
            'location' => $this->location,
            'location_qty' => $this->location_qty,
            'is_approved'        => $this->is_approved,
            'processing_details' => $this->processing_details,
        ];
    }
}
