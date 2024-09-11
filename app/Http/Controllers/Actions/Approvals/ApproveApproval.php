<?php

namespace App\Http\Controllers\Actions\Approvals;

use App\Enums\ApprovalModels;
use Illuminate\Http\JsonResponse;
use App\Enums\RequestApprovalStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;

class ApproveApproval extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke($modelType, $model)
    {
        $result = $model->updateApproval(['status' => RequestApprovalStatus::APPROVED, "date_approved" => Carbon::now()]);
        $nextApproval = $model->getNextPendingApproval();
        if ($nextApproval) {
            $nextApprovalUser = $nextApproval["user_id"];
            switch ($modelType) {
                case ApprovalModels::RequestItemProfiling->name:
                    User::find($nextApprovalUser);
                    $model->notify();
                    break;

            }
        } else {
            switch ($modelType) {
                case ApprovalModels::RequestItemProfiling->name:
                    User::find($model->created_by); // Notify the requestor
                    break;
            }
        }
        return new JsonResponse(["success" => $result["success"], "message" => $result['message']], $result["status_code"]);
    }
}
