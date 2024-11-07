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
        $uomSymbols = $this->getUomSymbolsAttribute();

        return [
            'id' => $this->id,
            'item_summary' => $this->item_summary,
            'item_code' => $this->item_code,
            'item_description' => $this->item_description,
            'thickness_val' => $this->thickness_val,
            'thickness_uom' => $uomSymbols['thickness_uom_symbol'],
            'length_val' => $this->length_val,
            'length_uom' => $uomSymbols['length_uom_symbol'],
            'width_val' => $this->width_val,
            'width_uom' => $uomSymbols['width_uom_symbol'],
            'height_val' => $this->height_val,
            'height_uom' => $uomSymbols['height_uom_symbol'],
            'outside_diameter_val' => $this->outside_diameter_val,
            'outside_diameter_uom' => $uomSymbols['outside_diameter_uom_symbol'],
            'inside_diameter_val' => $this->inside_diameter_val,
            'inside_diameter_uom' => $uomSymbols['inside_diameter_uom_symbol'],
            'volume_val' => $this->volume_val,
            'volume_uom' => $uomSymbols['volume_uom_symbol'],
            'specification' => $this->specification,
            'grade' => $this->grade,
            'color' => $this->color,
            'uom' => $this->uom,
            'convertable_units' => $this->convertable_unit->map(function ($uom) {
                return [
                    'id' => $uom->id,
                    'name' => $uom->name,
                    'symbol' => $uom->symbol,
                    'conversion' => $uom->conversion,
                ];
            }),
        ];
    }
}
