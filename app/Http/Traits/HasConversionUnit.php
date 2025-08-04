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
        return $this->uom->group->uoms;
    }
    public function getConvertedQuantity(UOM $toUom)
    {
        if ($this->{$this->uomIdColumn} === $toUom->id) {
            return $this->{$this->quantityColumn};
        }
        return UomConversionService::convert($this->{$this->quantityColumn}, $this->{$this->uomIdColumn}, $toUom->conversion);
    }
}
