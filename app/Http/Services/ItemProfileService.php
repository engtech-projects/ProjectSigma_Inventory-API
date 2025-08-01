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
        return collect([
            'item_description' => $item->item_description,
            'thickness' => $item->thickness,
            'length' => $item->length,
            'width' => $item->width,
            'height' => $item->height,
            'outside_diameter' => $item->outside_diameter,
            'inside_diameter' => $item->inside_diameter,
            'angle' => $item->angle,
            'size' => $item->size,
            'weight' => $item->weight,
            'volts' => $item->volts,
            'plates' => $item->plates,
            'part_number' => $item->part_number,
            'specification' => $item->specification,
            'volume' => $item->volume,
            'grade' => $item->grade,
            'color' => $item->color,
        ])->filter();
    }
}
