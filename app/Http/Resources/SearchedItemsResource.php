<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SearchedItemsResource extends JsonResource
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
            'item_summary' => $this->item_summary,
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
            'uom' => $this->uom,
            'uom_name' => $this->uom_full_name,
            'convertable_units' => $this->convertable_units,
        ];
    }
}
