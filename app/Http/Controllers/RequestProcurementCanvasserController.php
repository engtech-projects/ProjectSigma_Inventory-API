<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttachUsersProcurementRequest;
use App\Models\RequestProcurement;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class RequestProcurementCanvasserController extends Controller
{
    public function setCanvasser(AttachUsersProcurementRequest $request, RequestProcurement $requestProcurement)
    {
        $validated = $request->validated();
        try {
            $requestProcurement->canvassers()->sync($validated['user_ids']);
            return new JsonResponse([
                'success' => true,
                'message' => 'Users successfully attached as canvassers.',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to attach canvassers to procurement request', [
                'request_procurement_id' => $requestProcurement->id,
                'user_ids' => $validated['user_ids'],
                'error' => $e->getMessage()
            ]);
            return new JsonResponse([
                'success' => false,
                'message' => 'Attachment failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
