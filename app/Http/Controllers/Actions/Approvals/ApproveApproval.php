<?php

namespace App\Http\Controllers\Actions\Approvals;

use App\Enums\ApprovalModels;
use Illuminate\Http\JsonResponse;
use App\Enums\RequestApprovalStatus;
use App\Http\Controllers\Controller;
use App\Models\WarehouseTransaction;
use App\Notifications\RequestItemProfilingApprovedNotification;
use App\Notifications\RequestItemProfilingForApprovalNotification;
use App\Notifications\WarehouseTransactionApprovedNotification;
use App\Notifications\WarehouseTransactionForApprovalNotification;
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
                case ApprovalModels::WarehouseTransaction->name:
                    $model->notify(new WarehouseTransactionForApprovalNotification($request->bearerToken(), $model));
                    break;
            }
            switch ($modelType) {
                case ApprovalModels::WarehouseTransaction->name:
                    $model->notify(new WarehouseTransactionForApprovalNotification($request->bearerToken(), $model));
                    break;

            }
        } else {
            switch ($modelType) {
                case ApprovalModels::RequestItemProfiling->name:
                    $model->notify(new RequestItemProfilingApprovedNotification($request->bearerToken(), $model));
                    break;
                case ApprovalModels::WarehouseTransaction->name:
                    $model->notify(new WarehouseTransactionApprovedNotification($request->bearerToken(), $model));
                    break;
            }
            switch ($modelType) {
                case ApprovalModels::WarehouseTransaction->name:
                    $model->notify(new WarehouseTransactionApprovedNotification($request->bearerToken(), $model));
                    break;
            }
        }
        return new JsonResponse(["success" => $result["success"], "message" => $result['message']], $result["status_code"]);
    }
}
