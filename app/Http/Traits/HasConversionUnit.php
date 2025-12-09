<?php

namespace App\Http\Traits;

use App\Http\Services\UomConversionService;
use App\Models\UOM;
use InvalidArgumentException;

trait HasConversionUnit
{
    private $uomIdColumn = 'uom_id';
    private $quantityColumn = 'quantity';
    private $customConversionColumn = 'uom_conversion';
    /**
     * ==================================================
     * MODEL RELATIONSHIPS
     * ==================================================
     */
    public function uom()
    {
        return $this->belongsTo(UOM::class, $this->uomIdColumn)->withDefault();
    }
    /**
     * ==================================================
     * MODEL ATTRIBUTES
     * ==================================================
     */
    public function getConvertableUnitsAttribute()
    {
        return $this->uom->group?->uoms;
    }
    /**
     * ==================================================
     * MODEL FUNCTIONS
     * ==================================================
     */
    public function getConvertedQuantity(UOM $toUom)
    {
        // NO NEED TO CONVERT IF THE SOURCE AND TARGET UOM ARE THE SAME
        if ($this->{$this->uomIdColumn} === $toUom->id) {
            return $this->{$this->quantityColumn};
        }
        if (!$this->uom) {
            throw new InvalidArgumentException('Source UOM is not configured');
        }
        if ((float) $this->uom->conversion === 0.0 || (float) $toUom->conversion === 0.0) {
            throw new InvalidArgumentException('UOM conversion is not configured');
        }
        return UomConversionService::convert(
            (float) $this->{$this->quantityColumn},
            (float) $this->uom->conversion,
            (float) $toUom->conversion
        );
    }
}
