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
        $itemProfile = ItemProfile::where('is_approved', true)->get();
        $returnData = ItemListSummary::collection($itemProfile);
        return $returnData;
    }

    public static function itemListExport()
    {
        $masterListHeaders = [
            'Item Code',
            'Item Description',
            'Thicknesss',
            'Length',
            'Width',
            'Height',
            'Outside Diamater',
            'Inside Diamater',
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
            'UOM Conversion Value',
            'Item Group',
            'Sub Item Group',
            'Inventory Type',
            'Created At'
        ];
        $fileName = "storage/temp-export-generations/ItemList-" . Str::random(10);
        $excel = SimpleExcelWriter::create($fileName . ".xlsx");
        $excel->addHeader($masterListHeaders);
        $reportData = ExportService::itemListSummary()->resolve();
        foreach ($reportData as $row) {
            $excel->addRow($row);
        }
        $excel->close();
        Storage::disk('public')->delete($fileName . '.xlsx', now()->addMinutes(5));
        return '/' . $fileName . '.xlsx';
    }
}
