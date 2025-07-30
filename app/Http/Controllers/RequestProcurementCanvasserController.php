<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttachUsersProcurementRequest;
use App\Models\RequestProcurement;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\RequestProcurementDetailedResource;
use App\Models\RequestProcurementCanvasser;

class RequestProcurementCanvasserController extends Controller
{
    public function setCanvasser(AttachUsersProcurementRequest $request, RequestProcurement $requestProcurement)
    {
        // Use validated input
        $userId = $request->validated()['user_id'];

        // Verify user exists
        if (!\App\Models\User::find($userId)) {
            return response()->json([
                "message" => "User not found.",
                "success" => false,
            ]);
        }
        $currentCanvasser = $requestProcurement->canvasser;
        if ($currentCanvasser && $userId === $currentCanvasser->id) {
            return response()->json([
                'message' => 'The user is already assigned as canvasser to this procurement request.',
                'success' => false,
                "data" => new RequestProcurementDetailedResource($requestProcurement)
            ]);
        }
        DB::transaction(function () use ($userId, $requestProcurement) {
            RequestProcurementCanvasser::updateOrCreate(
                ['request_procurement_id' => $requestProcurement->id],
                ['user_id' => $userId]
            );
        });
        $requestProcurement->refresh();
        return response()->json([
            'message' => 'Successfully assigned new canvasser',
            'success' => true,
            "data" => new RequestProcurementDetailedResource($requestProcurement->load('canvasser'))
        ]);
    }
}
