<?php

namespace App\Http\Controllers\Actions\Approvals;

use App\Enums\ApprovalModels;
use Illuminate\Http\JsonResponse;
use App\Enums\RequestApprovalStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\RequestItemProfilingApprovedNotification;
use App\Notifications\RequestItemProfilingForApprovalNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ApproveApproval extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke($modelType, $model, Request $request)
    {
        $result = $model->updateApproval([
            'status' => RequestApprovalStatus::APPROVED, "date_approved" => Carbon::now()
        ]);

        $nextApproval = $model->getNextPendingApproval();
        if ($nextApproval) {
            switch ($modelType) {

                case ApprovalModels::RequestItemProfiling->name:
                    $model->notify(new RequestItemProfilingForApprovalNotification($request->bearerToken(), $model));
                    break;

            }
        } else {
            switch ($modelType) {

                case ApprovalModels::RequestItemProfiling->name:
                    $createdByUser = User::find($model->created_by);

                    if ($createdByUser) {
                        $createdByUser->notify(new RequestItemProfilingApprovedNotification($request->bearerToken(), $model));
                    } else {
                        return new JsonResponse([
                            'success' => false,
                            'message' => 'Creator of request not found'
                        ], JsonResponse::HTTP_NOT_FOUND);
                    }
                    break;
            }
        }
        return new JsonResponse(["success" => $result["success"], "message" => $result['message']], $result["status_code"]);
    }
}
