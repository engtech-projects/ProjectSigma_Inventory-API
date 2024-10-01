<?php

namespace App\Http\Services;

use App\Models\ItemProfile;
use App\Models\UOM;
use App\Models\UOMGroup;

class ItemProfileBulkUploadService
{
    public function parseCsv(array $rows)
    {
        $header = [
            'Item Description',
            'Thickness',
            'Thickness UOM',
            'Length',
            'Length UOM','Width',
            'Width UOM',
            'Height',
            'Height UOM',
            'Outside Diameter','Outside Diameter UOM',
            'Inside Diameter',
            'Inside Diameter UOM',
            'Volume',
            'Volume UOM',
            'Specification',
            'Grade',
            'Color',
            'UOM',
            'Item Group',
            'Sub Item Group',
            'Inventory Type'
        ];
        $headers = $rows[0] ?? [];

    $missingHeaders = array_diff($header, $headers);
    $extraHeaders = array_diff($headers, $header);

    if (!empty($missingHeaders) || !empty($extraHeaders)) {
        return [
            'error' => 'Incorrect CSV template. Missing Headers: ' . implode(', ', $missingHeaders) . '. Extra headers: ' . implode(', ', $extraHeaders)
        ];
    }

        $processed = [];
        $duplicates = [];
        $unprocessed = [];

        $validUOMGroup = UOMGroup::get();
        $validUOMs = UOM::get();

        $dataRows = array_slice($rows, 1); // Skip header row

        foreach ($dataRows as $row) {
            if (count($row) == count($header)) {
                $data = array_combine($header, $row);

                // Initialize the data structure for the row
                $filteredData = [
                    'item_description' => ['value' => $data['Item Description'] ?? null],
                    'thickness_val' => ['value' => $data['Thickness'] ?? null],
                    'thickness_uom' => ['value' => $data['Thickness UOM'] ?? null],
                    'length_val' => ['value' => $data['Length'] ?? null],
                    'length_uom' => ['value' => $data['Length UOM'] ?? null],
                    'width_val' => ['value' => $data['Width'] ?? null],
                    'width_uom' => ['value' => $data['Width UOM'] ?? null],
                    'height_val' => ['value' => $data['Height'] ?? null],
                    'height_uom' => ['value' => $data['Height UOM'] ?? null],
                    'outside_diameter_val' => ['value' => $data['Outside Diameter'] ?? null],
                    'outside_diameter_uom' => ['value' => $data['Outside Diameter UOM'] ?? null],
                    'inside_diameter_val' => ['value' => $data['Inside Diameter'] ?? null],
                    'inside_diameter_uom' => ['value' => $data['Inside Diameter UOM'] ?? null],
                    'volume_val' => ['value' => $data['Volume'] ?? null],
                    'volume_uom' => ['value' => $data['Volume UOM'] ?? null],
                    'specification' => ['value' => $data['Specification'] ?? null],
                    'grade' => ['value' => $data['Grade'] ?? null],
                    'color' => ['value' => $data['Color'] ?? null],
                    'uom' => ['value' => $data['UOM'] ?? null],
                    'item_group' => ['value' => $data['Item Group'] ?? null],
                    'sub_item_group' => ['value' => $data['Sub Item Group'] ?? null],
                    'inventory_type' => ['value' => $data['Inventory Type'] ?? null],
                ];

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

                $uomGroups = [
                    'uom'
                ];

                $isUnprocessed = false;

                // Check required fields
                foreach ($requiredFields as $field) {
                    if (empty($filteredData[$field]['value'])) {
                        $filteredData[$field]['error'] = "Missing or null required field: $field";
                        $isUnprocessed = true;
                    }
                }

                // Ensure at least one specification field is filled
                $atLeastOneSpecFilled = false;
                foreach ($specificationFields as $field) {
                    if (!empty($filteredData[$field]['value'])) {
                        $atLeastOneSpecFilled = true;
                    }
                }
                if (!$atLeastOneSpecFilled) {
                    $filteredData['specification']['error'] = "At least one specification field must be filled";
                    $isUnprocessed = true;
                }

                // Validate numeric fields
                foreach ($numericFields as $field) {
                    if (!empty($filteredData[$field]['value']) && !is_numeric($filteredData[$field]['value'])) {
                        $filteredData[$field]['error'] = "Field $field must be numeric";
                        $isUnprocessed = true;
                    }
                }

                // Validate UOM fields
                foreach ($uomFields as $uomField) {

                    if (!empty($filteredData[$uomField]['value'])) {
                        $uomValue = $filteredData[$uomField]['value'];
                        $isValid = $validUOMs->filter(function ($uom) use ($uomValue) {
                            return $uom->symbol == $uomValue;
                        })->first();

                        if (is_null($isValid)) {
                            $filteredData[$uomField]['error'] = "The value: $uomValue is not found in $uomField field.";
                            $isUnprocessed = true;
                        } else {
                            $filteredData[$uomField]['uom_id'] = $isValid->id;
                        }
                    }
                }

                foreach ($uomGroups as $uomGroup) {

                    if (!empty($filteredData[$uomGroup]['value'])) {
                        $uomValue = $filteredData[$uomGroup]['value'];
                        $isValid = $validUOMGroup->filter(function ($uom) use ($uomValue) {
                            return $uom->name == $uomValue;
                        })->first();

                        if (is_null($isValid)) {
                            $filteredData[$uomGroup]['error'] = "The value: $uomValue is not found in $uomField field.";
                            $isUnprocessed = true;
                        } else {
                            $filteredData[$uomGroup]['uom_group_id'] = $isValid->id;
                        }
                    }
                }

                // $filteredData['sku'] = $this->generateSKU($filteredData);

                // Add to unprocessed if there are errors, otherwise check for duplicates
                if ($isUnprocessed) {
                    $unprocessed[] = $filteredData;
                } else {
                    $existingItem = ItemProfile::where('item_description', $filteredData['item_description']['value'])->first();
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
