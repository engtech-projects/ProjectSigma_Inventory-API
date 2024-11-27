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
            'id' => $this->id,
            'item_code' => $this->item_code,
            'item_description' => $this->item_description,
            'thickness' => $this->thickness,
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height,
            'outside_diameter' => $this->outside_diameter,
            'inside_diameter' => $this->inside_diameter,
            'angle' => $this->angle,
            'size' => $this->size,
            'weight' => $this->weight,
            'volts' => $this->volts,
            'plates' => $this->plates,
            'part_number' => $this->part_number,
            'volume' => $this->volume,
            'specification' => $this->specification,
            'grade' => $this->grade,
            'color' => $this->color,
            'uom' => $this->uomFullName,
            'uom_conversion_group_id' => $this->uom_conversion_group_id,
            'uom_conversion_value' => $this->uom_conversion_value,
            'item_group' => $this->item_group,
            'sub_item_group' => $this->sub_item_group,
            'inventory_type' => $this->inventory_type,
            'active_status' => $this->active_status,
            'is_approved' => $this->is_approved,
        ];
    }

}
