<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkUploadItemProfile;
use App\Models\ItemProfile;
use App\Http\Requests\StoreItemProfileRequest;
use App\Http\Requests\UpdateItemProfileRequest;
use App\Http\Resources\ItemProfileResource;
use App\Utils\PaginateResourceCollection;
use Illuminate\Support\Facades\Storage;

class ItemProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $main = ItemProfile::get();
        $paginated = PaginateResourceCollection::paginate($main);
        $data = json_decode('{}');
        $data->message = "Request Item Profiling Successfully Fetched.";
        $data->success = true;
        $data->data = $paginated;
        return response()->json($data);
    }

    public function get()
    {
        $main = ItemProfile::where("is_approved", 1)->get();
        $requestResources = ItemProfileResource::collection($main)->collect();
        $paginated = PaginateResourceCollection::paginate($requestResources);

        return response()->json([
            'message' => 'Successfully fetched.',
            'success' => true,
            'data' => $paginated,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(StoreItemProfileRequest $request, ItemProfile $resource)
    {
        $saved = $resource->create($request->validated());
        return response()->json([
            'message' => $saved ? 'Item Profile Successfully created.' : 'Failed to create Item Profile.',
            'success' => (bool) $saved,
            'data' => $saved ?? null,
        ]);
    }


    /**
     * Display the specified resource.
     */
    public function show(ItemProfile $resource)
    {
        return response()->json([
            "message" => "Successfully fetched.",
            "success" => true,
            "data" => $resource
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function update(UpdateItemProfileRequest $request, ItemProfile $resource)
    {
        $resource->fill($request->validated());
        if ($resource->save()) {
            return response()->json([
                "message" => "Successfully updated.",
                "success" => true,
                "data" => $resource->refresh()
            ]);
        }
        return response()->json([
            "message" => "Failed to update.",
            "success" => false,
            "data" => $resource
        ], 400);
    }
    public function destroy(ItemProfile $resource)
    {
        if (!$resource) {
            return response()->json([
                'message' => 'Item Profile not found.',
                'success' => false,
                'data' => null
            ], 404);
        }

        $deleted = $resource->delete();

        $response = [
            'message' => $deleted ? 'Item Profile successfully deleted.' : 'Failed to delete Item Profile.',
            'success' => $deleted,
            'data' => $resource
        ];

        return response()->json($response, $deleted ? 200 : 400);
    }

    public function activate(ItemProfile $resource)
    {
        if ($resource->active_status === 'Active') {
            return response()->json([
                'message' => "Item profile is already active.",
                'item_profile' => $resource
            ], 200);
        }
        $resource->active_status = 'Active';
        $resource->save();

        return response()->json([
            'message' => 'Item profile activated successfully.',
            'item_profile' => $resource
        ]);
    }

    public function deactivate(ItemProfile $resource)
    {
        if ($resource->active_status === 'Inactive') {
            return response()->json([
                'message' => 'Item profile is already inactive.',
                'item_profile' => $resource
            ], 200);
        }
        $resource->active_status = 'Inactive';
        $resource->save();

        return response()->json([
            'message' => 'Item profile deactivated successfully.',
            'item_profile' => $resource
        ]);
    }

    public function uploadCsv(BulkUploadItemProfile $request)
    {
        // Store the uploaded file
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->storeAs('uploads', 'bulk_upload_' . time() . '.' . $file->getClientOriginalExtension());

            list($processed, $duplicates, $unprocessed) = $this->parseCsv($filePath);

            return response()->json([
                'message' => 'CSV File Uploaded Successfully.',
                'file_path' => $filePath,
                'processed' => $processed,
                'duplicates' => $duplicates,
                'unprocessed' => $unprocessed
            ], 200);
        }

        return response()->json([
            'message' => 'No file uploaded.'
        ], 400);
    }

    private function parseCsv($filePath)
    {
        $file = Storage::get($filePath);
        $rows = array_map('str_getcsv', explode("\n", $file));
        $header = array_shift($rows);

        $processed = [];
        $duplicates = [];
        $unprocessed = [];

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

                $requiredFields = [
                    'item_description', 'uom', 'item_group', 'sub_item_group', 'inventory_type'
                ];

                // Specification fields: At least one of these should be non-empty
                $specificationFields = [
                    'thickness_val', 'length_val', 'width_val', 'height_val',
                    'outside_diameter_val', 'inside_diameter_val', 'volume_val',
                    'color', 'grade', 'specification'
                ];

                $numericFields = [
                    'thickness_val', 'length_val', 'width_val', 'height_val',
                    'outside_diameter_val', 'inside_diameter_val', 'volume_val'
                ];

                $isUnprocessed = false;

                // Check required fields
                foreach ($requiredFields as $field) {
                    if (empty($filteredData[$field])) {
                        $isUnprocessed = true;
                        break;
                    }
                }

                // If all required fields are filled, check that at least one specification field is filled
                if (!$isUnprocessed) {
                    $atLeastOneSpecFilled = false;
                    foreach ($specificationFields as $field) {
                        if (!empty($filteredData[$field])) {
                            $atLeastOneSpecFilled = true;
                            break;
                        }
                    }

                    if (!$atLeastOneSpecFilled) {
                        $isUnprocessed = true;
                    }
                }

                // If the row passes required and specification field checks, validate numeric fields
                if (!$isUnprocessed) {
                    foreach ($numericFields as $field) {
                        if (!empty($filteredData[$field]) && !is_numeric($filteredData[$field])) {
                            $isUnprocessed = true;
                            break;
                        }
                    }
                }

                if ($isUnprocessed) {
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
                // Handle mismatched rows
                if (!empty(array_filter($row))) {
                    $unprocessed[] = array_combine($header, array_pad($row, count($header), null));
                }
            }
        }

        return [$processed, $duplicates, $unprocessed];
    }

}
