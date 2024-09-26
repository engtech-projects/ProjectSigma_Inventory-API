<?php

namespace App\Http\Services;

use App\Models\ItemProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ItemProfileBulkUploadService
{
    public function parseCsv($filePath)
    {
        $file = Storage::get($filePath);
        $rows = array_map('str_getcsv', explode("\n", $file));
        $header = array_shift($rows);

        $processed = [];
        $duplicates = [];
        $unprocessed = [];

        $validUOMs = DB::table('setup_uom')
            ->whereNull('deleted_at')
            ->pluck('symbol', 'name')
            ->toArray();

        $validUOMGroup = DB::table('setup_uom_group')
            ->whereNull('deleted_at')
            ->pluck('name')
            ->toArray();

        foreach ($rows as $row) {
            if (count($row) == count($header)) {
                $data = array_combine($header, $row);

                $filteredData = [
                    'item_description' => $data['Item Description'] ?? null,
                    'thickness_val' => $data['Thickness'] ?? null,
                    'thickness_uom' => $data['Thickness UOM'] ?? null,
                    'length_val' => $data['Length'] ?? null,
                    'length_uom' => $data['Length UOM'] ?? null,
                    'width_val' => $data['Width'] ?? null,
                    'width_uom' => $data['Width UOM'] ?? null,
                    'height_val' => $data['Height'] ?? null,
                    'height_uom' => $data['Height UOM'] ?? null,
                    'outside_diameter_val' => $data['Outside Diameter'] ?? null,
                    'outside_diameter_uom' => $data['Outside Diameter UOM'] ?? null,
                    'inside_diameter_val' => $data['Inside Diameter'] ?? null,
                    'inside_diameter_uom' => $data['Inside Diameter UOM'] ?? null,
                    'volume_val' => $data['Volume'] ?? null,
                    'volume_uom' => $data['Volume UOM'] ?? null,
                    'specification' => $data['Specification'] ?? null,
                    'grade' => $data['Grade'] ?? null,
                    'color' => $data['Color'] ?? null,
                    'uom' => $data['UOM'] ?? null,
                    'item_group' => $data['Item Group'] ?? null,
                    'sub_item_group' => $data['Sub Item Group'] ?? null,
                    'inventory_type' => $data['Inventory Type'] ?? null,
                ];

                // dd($validUOMGroup);

                $requiredFields = [
                    'item_description', 'uom', 'item_group', 'sub_item_group', 'inventory_type'
                ];

                $specificationFields = [
                    'thickness_val', 'length_val', 'width_val', 'height_val',
                    'outside_diameter_val', 'inside_diameter_val', 'volume_val',
                    'color', 'grade', 'specification'
                ];

                $numericFields = [
                    'thickness_val', 'length_val', 'width_val', 'height_val',
                    'outside_diameter_val', 'inside_diameter_val', 'volume_val'
                ];

                $uomFields = [
                    'thickness_uom', 'length_uom', 'width_uom', 'height_uom',
                    'outside_diameter_uom', 'inside_diameter_uom', 'volume_uom'
                ];

                $isUnprocessed = false;
                $errorMessages = [];

                foreach ($requiredFields as $field) {
                    if (empty($filteredData[$field])) {
                        $isUnprocessed = true;
                        $errorMessages[] = "Missing or null required field: $field";
                    }
                }

                if (!$isUnprocessed) {
                    $atLeastOneSpecFilled = false;
                    foreach ($specificationFields as $field) {
                        if (!empty($filteredData[$field])) {
                            $atLeastOneSpecFilled = true;
                        }
                    }

                    if (!$atLeastOneSpecFilled) {
                        $isUnprocessed = true;
                        $errorMessages[] = "At least one specification field must be filled";
                    }
                }

                if (!$isUnprocessed) {
                    foreach ($numericFields as $field) {
                        if (!empty($filteredData[$field]) && !is_numeric($filteredData[$field])) {
                            $isUnprocessed = true;
                            $errorMessages[] = "Field $field must be numeric";
                        }
                    }
                }

                foreach ($uomFields as $uomField) {
                    if (!empty($filteredData[$uomField])) {
                        $uomValue = $filteredData[$uomField];
                        // Check if UOM is valid by either 'name' or 'symbol'
                        if (!in_array($uomValue, $validUOMs) && !array_key_exists($uomValue, $validUOMs)) {
                            $isUnprocessed = true;
                            $errorMessages[] = "The value: $uomValue is not found in $uomField field.";
                        }
                    }
                }

                if (!empty($filteredData['uom'])) {
                    $mainUomValue = $filteredData['uom'];
                    // Check if UOM group is valid
                    if (!in_array($mainUomValue, $validUOMGroup)) {
                        $isUnprocessed = true;
                        $errorMessages[] = "Invalid input: $mainUomValue in UOM field.";
                    }
                }

                if ($isUnprocessed) {
                    $filteredData['errors'] = $errorMessages;
                    $unprocessed[] = $filteredData;
                } else {
                    $existingItem = ItemProfile::where('item_description', $filteredData['item_description'])->first();
                    if ($existingItem) {
                        $duplicates[] = $filteredData;
                    } else {
                        $processed[] = $filteredData;
                    }
                }
            } else {
                if (!empty(array_filter($row))) {
                    $unprocessed[] = array_combine($header, array_pad($row, count($header), null));
                }
            }
        }

        return [$processed, $duplicates, $unprocessed];
    }
}
