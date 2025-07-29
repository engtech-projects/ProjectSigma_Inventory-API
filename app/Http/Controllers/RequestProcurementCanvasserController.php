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
        $userId = $request->input('user_id');
        if (!$userId) {
            return response()->json([
                "message" => "Failed to assign canvasser.",
                "success" => false,
            ]);
        }
        $userId = intval($userId);
        $currentCanvasser = $requestProcurement->canvasser;
        if ($currentCanvasser && $userId === $currentCanvasser->id) {
            return response()->json([
                'message' => 'The user is already assigned as canvasser to this procurement request.',
                'success' => false,
                "data" => new RequestProcurementDetailedResource($requestProcurement)
            ]);
        }
        DB::transaction(function () use ($userId, $requestProcurement) {
            RequestProcurementCanvasser::where('request_procurement_id', $requestProcurement->id)->delete();
            RequestProcurementCanvasser::create([
                'request_procurement_id' => $requestProcurement->id,
                'user_id' => $userId,
            ]);
        });
        $requestProcurement->refresh();
        return response()->json([
            'message' => 'Successfully assigned new canvasser',
            'success' => true,
            "data" => new RequestProcurementDetailedResource($requestProcurement->load('canvasser'))
        ]);
    }
}
