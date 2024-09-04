<?php

namespace App\Http\Services;

use App\Models\ItemProfile;

class ItemProfileService
{
    public static function getSimilarItems($itemDescription)
    {
        return ItemProfile::where('item_description', "like", "%".$itemDescription."%")->isApproved()->get();

    }

}
