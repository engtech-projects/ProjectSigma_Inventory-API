<?php

namespace App\Http\Controllers;

use App\Http\Services\ExportService;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function itemListGenerate(Request $request)
    {
        $downloadUrl = ExportService::itemListExport($request);
        return response()->json(
            [
                "success" => true,
                'url' => $downloadUrl,
                'message' => "Successfully Download."
            ]
        );
    }
}
