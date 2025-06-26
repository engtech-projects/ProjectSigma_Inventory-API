<?php

namespace App\Http\Services;

use App\Http\Resources\Exports\ItemListSummary;
use App\Models\ItemProfile;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Illuminate\Support\Str;

class ExportService
{
    public static function itemListSummary()
    {
        return ItemListSummary::collection(
            ItemProfile::query()->isApproved()->get()
        );
    }

    public static function itemListExport()
    {
        $masterListHeaders = [
            'Item Code',
            'Item Description',
            'Thickness',
            'Length',
            'Width',
            'Height',
            'Outside Diameter',
            'Inside Diameter',
            'Angle',
            'Size',
            'Specification',
            'Volume',
            'Weight',
            'Grade',
            'Volts',
            'Plates',
            'Part Number',
            'Color',
            'UOM',
            'Item Group',
            'Sub Item Group',
            'Inventory Type',
            'Created At'
        ];
        $relativePath = 'temp-export-generations/ItemList-' . Str::random(10) . '.xlsx';
        Storage::disk('public')->makeDirectory('temp-export-generations');
        $fullPath = Storage::disk('public')->path($relativePath);
        $excel = SimpleExcelWriter::create($fullPath);
        $excel->addHeader($masterListHeaders);
        $reportData = ExportService::itemListSummary()->resolve();
        $excel->addRows($reportData);
        $excel->close();
        Storage::disk('public')->delete('/storage/' . $relativePath . '.xlsx', now()->addMinutes(5));
        return '/storage/' . $relativePath;
    }
}
