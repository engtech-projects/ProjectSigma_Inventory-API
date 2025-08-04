<?php

namespace App\Http\Traits;

use App\Http\Services\UomConversionService;
use App\Models\UOM;

trait HasConversionUnit
{
    private $uomIdColumn = 'uom_id';
    private $quantityColumn = 'quantity';
    private $customConversionColumn = 'uom_conversion';

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
    public function getConvertedQuantity($toUomId)
    {
        if ($this->{$this->uomIdColumn} === $toUomId) {
            return $this->{$this->quantityColumn};
        }
        return UomConversionService::convert($this->{$this->quantityColumn}, $this->{$this->uomIdColumn}, $toUomId);
    }
}
