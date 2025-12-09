<?php

namespace App\Http\Controllers;

use App\Http\Services\ExportService;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function itemListGenerate(Request $request)
    {
        try {
            $downloadUrl = ExportService::itemListExport($request);
            return response()->json([
                "success" => true,
                'url' => url($downloadUrl),
                'message' => "Successfully Download."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                'url' => null,
                'message' => "Export failed: " . $e->getMessage()
            ], 500);
        }
    }
}
