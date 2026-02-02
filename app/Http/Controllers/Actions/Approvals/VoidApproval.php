<?php

namespace App\Http\Controllers\Actions\Approvals;

use App\Enums\ApprovalModels;
use Illuminate\Http\JsonResponse;
use App\Enums\RequestStatuses;
use App\Http\Controllers\Controller;
use App\Http\Requests\VoidApprovalRequest;
use App\Notifications\RequestBOMVoidedNotification;
use Carbon\Carbon;

class VoidApproval extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke($modelType, $model, VoidApprovalRequest $request)
    {
        $attribute = $request->validated();
        $result = collect($model->updateApproval([
            'status' => RequestStatuses::VOIDED,
            'remarks' => $attribute['remarks'],
            "date_voided" => Carbon::now()
        ]));

        switch ($modelType) {
            case ApprovalModels::RequestBOM->name:
                $model->notify(new RequestBOMVoidedNotification($request->bearerToken(), $model));
                break;
            default:
                break;
        }
        return new JsonResponse(["success" => $result["success"], "message" => $result['message']], JsonResponse::HTTP_OK);
    }
}
