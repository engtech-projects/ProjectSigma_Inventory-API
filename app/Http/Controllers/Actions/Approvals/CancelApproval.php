<?php

namespace App\Http\Controllers\Actions\Approvals;

use App\Enums\ApprovalModels;
use Illuminate\Http\JsonResponse;
use App\Enums\RequestStatuses;
use App\Http\Controllers\Controller;
use App\Http\Requests\CancelApprovalRequest;
use App\Notifications\RequestBOMCancelledNotification;
use Carbon\Carbon;

class CancelApproval extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke($modelType, $model, CancelApprovalRequest $request)
    {
        $attribute = $request->validated();
        $result = collect($model->updateApproval([
            'status' => RequestStatuses::CANCELLED,
            'remarks' => $attribute['remarks'],
            "date_cancelled" => Carbon::now()
        ]));

        switch ($modelType) {
            case ApprovalModels::RequestBOM->name:
                $model->notify(new RequestBOMCancelledNotification($request->bearerToken(), $model));
                break;

            default:
                break;
        }
        return new JsonResponse(["success" => $result["success"], "message" => $result['message']], JsonResponse::HTTP_OK);
    }
}
