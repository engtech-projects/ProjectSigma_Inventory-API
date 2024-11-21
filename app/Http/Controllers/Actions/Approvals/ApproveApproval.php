<?php

namespace App\Http\Controllers\Actions\Approvals;

use App\Enums\ApprovalModels;
use Illuminate\Http\JsonResponse;
use App\Enums\RequestApprovalStatus;
use App\Http\Controllers\Controller;
use App\Notifications\RequestItemProfilingForApprovalNotification;
use App\Notifications\RequestSupplierApprovedNotification;
use App\Notifications\RequestSupplierForApprovalNotification;
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
            'status' => RequestApprovalStatus::APPROVED,
            "date_approved" => Carbon::now()
        ]);

        $nextApproval = $model->getNextPendingApproval();
        if ($nextApproval) {
            $nextApprovalUser = $nextApproval["user_id"];
            switch ($modelType) {
                case ApprovalModels::RequestItemProfiling->name:
                    $model->notify(new RequestItemProfilingForApprovalNotification($request->bearerToken(), $model));
                    break;
                case ApprovalModels::RequestSupplier->name:
                    $model->notify(new RequestSupplierForApprovalNotification($request->bearerToken(), $model));
                    break;

            }
        } else {
            switch ($modelType) {
                case ApprovalModels::RequestItemProfiling->name:
                    $model->notify(new RequestSupplierApprovedNotification($request->bearerToken(), $model));
                    break;
            }
        }
        return new JsonResponse(["success" => $result["success"], "message" => $result['message']], $result["status_code"]);
    }
}
