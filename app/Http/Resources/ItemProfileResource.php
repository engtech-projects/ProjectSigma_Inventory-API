<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'id' => $this->id,
            'sku' => $this->sku,
            'item_description' => $this->item_description,
            'thickness_val' => $this->thickness_val,
            'length_val' => $this->length_val,
            'width_val' => $this->width_val,
            'height_val' => $this->height_val,
            'outside_diameter_val' => $this->outside_diameter_val,
            'inside_diameter_val' => $this->inside_diameter_val,
            'specification' => $this->specification,
            'volume' => $this->volume,
            'grade' => $this->grade,
            'color' => $this->color,
            'uom' => $this->uom,
            'uom_conversion_id' => $this->uom_conversion_id,
            'uom_conversion_value' => $this->uom_conversion_value,
            'inventory_type' => $this->inventory_type,
            'active_status' => $this->active_status,
            'is_approved' => $this->is_approved,
        ];
    }
}
