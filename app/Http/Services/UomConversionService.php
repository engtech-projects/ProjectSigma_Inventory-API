<?php

namespace App\Http\Services;

class UomConversionService
{
    /**
     * Convert a quantity from one unit of measure to another.
     *
     * @param float $quantity The quantity to convert.
     * @param float $fromConversion The conversion factor from the original unit.
     * @param float $toConversion The conversion factor to the target unit.
     * @return float The converted quantity.
     */
    public static function convert($quantity, $fromConversion, $toConversion)
    {
        return $quantity * ($fromConversion / $toConversion);
    }
}
