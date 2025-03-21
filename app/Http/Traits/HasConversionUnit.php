<?php

namespace App\Http\Traits;
use App\Models\UOM;

trait HasConversionUnit
{
    public function getConvertableUnitsAttribute()
    {
        $uom = $this->uom instanceof UOM ? $this->uom : UOM::find($this->uom);

        if (!$uom || !$uom->group_id) {
            return [];
        }

        return UOM::where('group_id', $uom->group_id)
            ->get()
            ->map(function ($uom) {
                return [
                    'id' => $uom->id,
                    'name' => $uom->name,
                    'symbol' => $uom->symbol,
                    'conversion' => $uom->conversion,
                ];
            })
            ->toArray();
    }
}
