<?php

namespace App\Http\Services;

use App\Enums\InventoryType;
use App\Models\ItemProfile;
use App\Models\UOM;

class ItemProfileBulkUploadService
{
    public function parseCsv(array $rows)
    {
        $header = [
            'Item Description',
            'Thickness',
            'Thickness UOM',
            'Length',
            'Length UOM',
            'Width',
            'Width UOM',
            'Height',
            'Height UOM',
            'Outside Diameter',
            'Outside Diameter UOM',
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
                    'item_description',
                    'uom',
                    'item_group',
                    'sub_item_group',
                    'inventory_type'
                ];

                $specificationFields = [
                    'thickness_val',
                    'length_val',
                    'width_val',
                    'height_val',
                    'outside_diameter_val',
                    'inside_diameter_val',
                    'volume_val',
                    'color',
                    'grade',
                    'specification'
                ];

                $numericFields = [
                    'thickness_val',
                    'length_val',
                    'width_val',
                    'height_val',
                    'outside_diameter_val',
                    'inside_diameter_val',
                    'volume_val'
                ];

                $uomFields = [
                    'thickness_uom',
                    'length_uom',
                    'width_uom',
                    'height_uom',
                    'outside_diameter_uom',
                    'inside_diameter_uom',
                    'volume_uom',
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
                    $filteredData['specification']['error'] = "At least one specification field must be filled.";
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
                            $filteredData[$uomField]['error'] = "The value: $uomValue is not a valid unit of measurement. Please check the available UOM options in the setup for valid inputs.";
                            $isUnprocessed = true;
                        } else {
                            $filteredData[$uomField]['uom_id'] = $isValid->id;
                        }
                    }
                }

                $validInventoryValues = array_column(InventoryType::cases(), 'value');
                $validOptions = implode(', ', $validInventoryValues);
                if (!in_array($filteredData['inventory_type']['value'], $validInventoryValues)) {
                    $filteredData['inventory_type']['error'] = "Invalid inventory type: " . $filteredData['inventory_type']['value'] . ". Valid options are: $validOptions.";
                    $isUnprocessed = true;
                }


                $filteredData['item_code'] = $this->generateSKU($filteredData);

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

    private function generateSKU(array $filteredData): string
    {
        $skuPrefix = strtoupper(substr($filteredData['item_description']['value'] ?? '', 0, 3));

        foreach ([
            ['thickness_val', 'thickness_uom'],
            ['length_val', 'length_uom'],
            ['width_val', 'width_uom'],
            ['height_val', 'height_uom'],
            ['outside_diameter_val', 'outside_diameter_uom'],
            ['inside_diameter_val', 'inside_diameter_uom'],
            ['volume_val', 'volume_uom']
        ] as [$valField, $uomField]) {
            $value = $filteredData[$valField]['value'] ?? null;
            $uom = $filteredData[$uomField]['value'] ?? null;

            if ($value && $uom) {
                return $skuPrefix . strtoupper($value . preg_replace('/\s+/', '', $uom));
            }
        }

        foreach (['specification', 'grade', 'color'] as $specField) {
            $specFieldsValue = $filteredData[$specField]['value'] ?? null;

            if ($specFieldsValue) {
                return $skuPrefix . strtoupper(substr(preg_replace('/\s+/', '', $specFieldsValue), -3));
            }
        }

        return $skuPrefix;
    }


    public function selectedItems(array $processed)
    {
        $itemsToInsert = array_map(fn ($item) => [
            'item_code' => $item['item_code'],
            'item_description' => $item['item_description']['value'],
            'thickness_val' => $item['thickness_val']['value'] ?? null,
            'thickness_uom' => $item['thickness_uom']['uom_id'] ?? null,
            'length_val' => $item['length_val']['value'] ?? null,
            'length_uom' => $item['length_uom']['uom_id'] ?? null,
            'width_val' => $item['width_val']['value'] ?? null,
            'width_uom' => $item['width_uom']['uom_id'] ?? null,
            'height_val' => $item['height_val']['value'] ?? null,
            'height_uom' => $item['height_uom']['uom_id'] ?? null,
            'outside_diameter_val' => $item['outside_diameter_val']['value'] ?? null,
            'outside_diameter_uom' => $item['outside_diameter_uom']['uom_id'] ?? null,
            'inside_diameter_val' => $item['inside_diameter_val']['value'] ?? null,
            'inside_diameter_uom' => $item['inside_diameter_uom']['uom_id'] ?? null,
            'volume_val' => $item['volume_val']['value'] ?? null,
            'volume_uom' => $item['volume_uom']['uom_id'] ?? null,
            'specification' => $item['specification']['value'] ?? null,
            'grade' => $item['grade']['value'] ?? null,
            'color' => $item['color']['value'] ?? null,
            'uom' => $item['uom']['uom_group_id'],
            'item_group' => $item['item_group']['value'],
            'sub_item_group' => $item['sub_item_group']['value'],
            'inventory_type' => $item['inventory_type']['value'],
            'is_approved' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ], $processed);

        ItemProfile::insert($itemsToInsert);
    }

}
