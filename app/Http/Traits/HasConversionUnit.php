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
        return $this->uom->group?->uoms;
    }
    public function getConvertedQuantity(UOM $toUom)
    {
        if ($this->{$this->uomIdColumn} === $toUom->id) {
            return $this->{$this->quantityColumn};
        }
        if ($this->uom->conversion == 0 || $toUom->conversion == 0) {
            throw new \InvalidArgumentException('UOM conversion is not configured');
        }
        return UomConversionService::convert($this->{$this->quantityColumn}, $this->uom->conversion, $toUom->conversion);
    }
}
