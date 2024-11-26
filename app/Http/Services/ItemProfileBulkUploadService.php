<?php

namespace App\Http\Services;

use App\Enums\InventoryType;
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
            'Length',
            'Width',
            'Height',
            'Outside Diameter',
            'Inside Diameter',
            'Angle',
            'Size',
            'Volume',
            'Weight',
            'Grade',
            'Volts',
            'Plates',
            'Part Number',
            'Color',
            'Specification',
            'UOM',
            'Sub Item Group',
            'Item Group',
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
                    'thickness' => ['value' => $data['Thickness'] ?? null],
                    'length' => ['value' => $data['Length'] ?? null],
                    'width' => ['value' => $data['Width'] ?? null],
                    'height' => ['value' => $data['Height'] ?? null],
                    'outside_diameter' => ['value' => $data['Outside Diameter'] ?? null],
                    'inside_diameter' => ['value' => $data['Inside Diameter'] ?? null],
                    'angle' => ['value' => $data['Angle'] ?? null],
                    'size' => ['value' => $data['Size'] ?? null],
                    'volume' => ['value' => $data['Volume'] ?? null],
                    'weight' => ['value' => $data['Weight'] ?? null],
                    'grade' => ['value' => $data['Grade'] ?? null],
                    'volts' => ['value' => $data['Volts'] ?? null],
                    'plates' => ['value' => $data['Plates'] ?? null],
                    'part_number' => ['value' => $data['Part Number'] ?? null],
                    'color' => ['value' => $data['Color'] ?? null],
                    'specification' => ['value' => $data['Specification'] ?? null],
                    'uom' => ['value' => $data['UOM'] ?? null],
                    'sub_item_group' => ['value' => $data['Sub Item Group'] ?? null],
                    'item_group' => ['value' => $data['Item Group'] ?? null],
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
                    'thickness',
                    'length',
                    'width',
                    'height',
                    'outside_diameter',
                    'inside_diameter',
                    'volume',
                    'color',
                    'grade',
                    'specification',
                    'weight',
                    'volts',
                    'plates',
                    'part_number',
                    'angle',
                    'size'
                ];

                $uomFields = [
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


                $filteredData['item_code'] = $this->generateItemCode($filteredData);

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

    private function generateItemCode(array $filteredData): string
    {
        $skuPrefix = strtoupper(substr($filteredData['item_description']['value'] ?? '', 0, 3));

        foreach ([
            'thickness',
            'length',
            'width',
            'height',
            'outside_diameter',
            'inside_diameter',
            'volume',
            'weight',
            'volts',
            'plates',
            'part_number',
            'angle',
            'size'
        ] as $field) {
            $value = $filteredData[$field]['value'] ?? null;

            if ($value) {
                return $skuPrefix . strtoupper(preg_replace('/\s+/', '', $value));
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
            'thickness' => $item['thickness']['value'] ?? null,
            'length' => $item['length']['value'] ?? null,
            'width' => $item['width']['value'] ?? null,
            'height' => $item['height']['value'] ?? null,
            'outside_diameter' => $item['outside_diameter']['value'] ?? null,
            'inside_diameter' => $item['inside_diameter']['value'] ?? null,
            'volume' => $item['volume']['value'] ?? null,
            'specification' => $item['specification']['value'] ?? null,
            'grade' => $item['grade']['value'] ?? null,
            'color' => $item['color']['value'] ?? null,
            'uom' => $item['uom']['uom_id'],
            'item_group' => $item['item_group']['value'],
            'sub_item_group' => $item['sub_item_group']['value'],
            'inventory_type' => $item['inventory_type']['value'],
            'volts' => $item['volts']['value'] ?? null,
            'plates' => $item['plates']['value'] ?? null,
            'part_number' => $item['part_number']['value'] ?? null,
            'angle' => $item['angle']['value'] ?? null,
            'size' => $item['size']['value'] ?? null,
            'is_approved' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ], $processed);

        ItemProfile::insert($itemsToInsert);
    }
}
