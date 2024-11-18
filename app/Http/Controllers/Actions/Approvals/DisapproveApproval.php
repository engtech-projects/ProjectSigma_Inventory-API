<?php

namespace App\Http\Controllers\Actions\Approvals;

use App\Enums\ApprovalModels;
use Illuminate\Http\JsonResponse;
use App\Enums\RequestApprovalStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\DisapproveApprovalRequest;
use App\Notifications\RequestBOMDeniedNotification;
use App\Notifications\RequestItemProfilingDeniedNotification;
use App\Notifications\WarehouseTransactionDeniedNotification;
use Carbon\Carbon;

class DisapproveApproval extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke($modelType, $model, DisapproveApprovalRequest $request)
    {
        $attribute = $request->validated();
        $result = collect($model->updateApproval([
            'status' => RequestApprovalStatus::DENIED,
            'remarks' => $attribute['remarks'],
            "date_denied" => Carbon::now()
        ]));

        switch ($modelType) {
            case ApprovalModels::RequestItemProfiling->name:
                $model->notify(new RequestItemProfilingDeniedNotification($request->bearerToken(), $model));
                break;
            case ApprovalModels::WarehouseTransaction->name:
                $model->notify(new WarehouseTransactionDeniedNotification($request->bearerToken(), $model));
                break;
            case ApprovalModels::RequestBOM->name:
                $model->notify(new RequestBOMDeniedNotification($request->bearerToken(), $model));
                break;

            default:
                break;
        }
        return new JsonResponse(["success" => $result["success"], "message" => $result['message']], JsonResponse::HTTP_OK);
    }
}
