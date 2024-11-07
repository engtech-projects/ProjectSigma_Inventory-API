<?php

namespace App\Http\Services;

use App\Models\ItemProfile;

class ItemProfileService
{
    public static function getSimilarItems($itemDescription)
    {
        return ItemProfile::where('item_description', "like", "%".$itemDescription."%")->isApproved()->get();

    }

    public function getItemSummary($item)
    {
        $uomSymbols = $item->getUomSymbolsAttribute();
        return collect([
            'item_description' => $item->item_description,
            'thickness_val' => $item->thickness_val,
            'thickness_uom' => $uomSymbols['thickness_uom_symbol'],
            'length_val' => $item->length_val,
            'length_uom' => $uomSymbols['length_uom_symbol'],
            'width_val' => $item->width_val,
            'width_uom' => $uomSymbols['width_uom_symbol'],
            'height_val' => $item->height_val,
            'height_uom' => $uomSymbols['height_uom_symbol'],
            'outside_diameter_val' => $item->outside_diameter_val,
            'outside_diameter_uom' => $uomSymbols['outside_diameter_uom_symbol'],
            'inside_diameter_val' => $item->inside_diameter_val,
            'inside_diameter_uom' => $uomSymbols['inside_diameter_uom_symbol'],
            'specification' => $item->specification,
            'volume_val' => $item->volume_val,
            'volume_uom' => $uomSymbols['volume_uom_symbol'],
            'grade' => $item->grade,
            'color' => $item->color,
        ])->filter();
    }


}
