<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttachUsersProcurementRequest;
use App\Models\RequestProcurement;
use App\Models\RequestProcurementCanvasser;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\RequestProcurementDetailedResource;

class RequestProcurementCanvasserController extends Controller
{
    public function setCanvasser(AttachUsersProcurementRequest $request, RequestProcurement $requestProcurement)
    {
        $userId = $request->input('user_id');
        $currentCanvasser = $requestProcurement->canvasser;
        $currentUserId = $currentCanvasser?->user_id;
        if ($userId) {
            $intUserId = intval($userId);
            if ($intUserId === $currentUserId) {
                return response()->json([
                    'message' => 'The user is already assigned as canvasser to this procurement request.',
                    'success' => false,
                    "data" => new RequestProcurementDetailedResource($requestProcurement)
                ]);
            }

            DB::transaction(function () use ($userId, $requestProcurement) {
                RequestProcurementCanvasser::where("request_procurement_id", $requestProcurement->id)->delete();

                RequestProcurementCanvasser::create([
                    'request_procurement_id' => $requestProcurement->id,
                    'user_id' => $userId,
                ]);
            });

            return response()->json([
                'message' => 'Successfully assigned new canvasser',
                'success' => true,
                "data" => new RequestProcurementDetailedResource($requestProcurement->load('canvasser'))
            ]);
        }

        return response()->json([
            "message" => "Failed to assign canvasser.",
            "success" => false,
        ]);
    }
}
