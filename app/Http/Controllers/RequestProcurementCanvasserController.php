<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttachUsersProcurementRequest;
use App\Models\RequestProcurement;
use Illuminate\Http\JsonResponse;

class RequestProcurementCanvasserController extends Controller
{
    public function setCanvasser(AttachUsersProcurementRequest $request, RequestProcurement $requestProcurement)
    {
        $validated = $request->validated();
        try {
            $requestProcurement->canvassers()->attach($validated['user_ids']);
            return new JsonResponse([
                'success' => true,
                'message' => 'Users successfully attached as canvassers.',
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Attachment failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
