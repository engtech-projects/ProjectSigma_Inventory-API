<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkUploadItemProfile;
use App\Http\Services\ItemProfileBulkUploadService;
use Illuminate\Http\Request;

class ItemProfileBulkUploadController extends Controller
{
    protected $itemProfileBulkUploadService;

    public function __construct(ItemProfileBulkUploadService $itemProfileBulkUploadService)
    {
        $this->itemProfileBulkUploadService = $itemProfileBulkUploadService;
    }
    public function bulkUpload(BulkUploadItemProfile $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('file')) {
            $file = $validated['file'];
            $fileContent = file_get_contents($file->getRealPath());
            $rows = array_map('str_getcsv', explode("\n", $fileContent));

            $result = $this->itemProfileBulkUploadService->parseCsv($rows);
            if (isset($result['error'])) {
                return response()->json([
                    'message' => 'Failed to parse CSV.',
                    'error' => $result['error']
                ], 400);
            }

            list($processed, $duplicates, $unprocessed) = $this->itemProfileBulkUploadService->parseCsv($rows);

            return response()->json([
                'message' => 'CSV File Parsed Successfully.',
                'processed' => $processed,
                'duplicates' => $duplicates,
                'unprocessed' => $unprocessed
            ], 200);
        }

        return response()->json([
            'message' => 'No file uploaded.'
        ], 400);
    }

}
