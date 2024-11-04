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
            'item_code' => $this->item_code,
            'item_description' => $this->item_description,
            'thickness_val' => $this->thickness_val,
            'thickness_uom' => $this->ThicknessUomSymbol,
            'length_val' => $this->length_val,
            'length_uom' => $this->LengthUomSymbol,
            'width_val' => $this->width_val,
            'width_uom' => $this->WidthUomSymbol,
            'height_val' => $this->height_val,
            'height_uom' => $this->HeightUomSymbol,
            'outside_diameter_val' => $this->outside_diameter_val,
            'outside_diameter_uom' => $this->OutsideDiameterUomSymbol,
            'inside_diameter_val' => $this->inside_diameter_val,
            'inside_diameter_uom' => $this->InsideDiameterUomSymbol,
            'volume_val' => $this->volume_val,
            'volume_uom' => $this->VolumeUomSymbol,
            'specification' => $this->specification,
            'grade' => $this->grade,
            'color' => $this->color,
            'uom' => $this->uomFullName,
            'uom_detail' => $this->uomName,
            'active_status' => $this->active_status,
        ];
    }
}
