<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkUploadItemProfile;
use App\Http\Services\ItemProfileBulkUploadService;

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
            $filePath = $file->storeAs('uploads', 'bulk_upload_' . time() . '.' . $file->getClientOriginalExtension());

            list($processed, $duplicates, $unprocessed) = $this->itemProfileBulkUploadService->parseCsv($filePath);

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
}
