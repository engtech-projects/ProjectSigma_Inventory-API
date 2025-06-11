<?php

namespace App\Http\Controllers;

use App\Http\Services\ExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function itemListGenerate(Request $request)
    {
        return new JsonResponse(ExportService::itemListExport($request));
    }
}
