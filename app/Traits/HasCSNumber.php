<?php

namespace App\Traits;

use App\Models\RequestCanvassSummary;

trait HasCSNumber
{
    public function generateCsNumber(): string
    {
        $year = now()->year;
        $initials = 'NJTT'; // static for now
        $prefix = "CS-{$year}-{$initials}";

        $latest = RequestCanvassSummary::query()
            ->whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();

        $latestSeries = 0;

        if ($latest && !empty($latest->cs_number)) {
            // Extract the last 4-digit series from metadata
            preg_match('/(\d{4})$/', $latest->cs_number, $matches);
            $latestSeries = isset($matches[1]) ? (int) $matches[1] : 0;
        }

        $newSeries = str_pad($latestSeries + 1, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$newSeries}";
    }

    public function csNumber(RequestCanvassSummary $request): string
    {
        return $this->generateCsNumber($request);
    }
}
