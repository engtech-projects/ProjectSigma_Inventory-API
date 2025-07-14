<?php

namespace App\Traits;

trait HasReferenceNumber
{
    /**
     * Flexible generator for reference or quotation numbers.
     *
     * @param string $column e.g. 'reference_no' or 'quotation_no'
     * @param callable|null $formatter e.g. fn($prefix, $datePart, $number) => "MRR-{$datePart}-CENTRAL-{$number}"
     * @param array $options
     *    - prefix: e.g. 'MRR' or 'RPQ'
     *    - dateFormat: e.g. 'Y' or 'Y-m'
     * @return string
     */
    public static function generateReferenceNumber(string $column, callable $formatter = null, array $options = [])
    {
        $prefix = $options['prefix'] ?? 'REF';
        $dateFormat = $options['dateFormat'] ?? 'Y-m';
        $pattern = "{$prefix}-" . date($dateFormat) . '-%';
        $lastRecord = self::where($column, 'like', $pattern)
            ->latest('created_at')
            ->lockForUpdate()
            ->first();
        $newNumber = $lastRecord ? ((int)substr($lastRecord->$column, -4) + 1) : 1;
        if ($formatter) {
            return $formatter($prefix, date($dateFormat), str_pad($newNumber, 4, '0', STR_PAD_LEFT));
        }
        // default format
        return "{$prefix}-" . date($dateFormat) . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
