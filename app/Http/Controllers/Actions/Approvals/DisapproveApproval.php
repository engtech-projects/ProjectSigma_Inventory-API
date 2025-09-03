<?php

namespace App\Http\Controllers\Actions\Approvals;

use App\Enums\ApprovalModels;
use Illuminate\Http\JsonResponse;
use App\Enums\RequestApprovalStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\DisapproveApprovalRequest;
use App\Models\User;
use App\Notifications\RequestBOMDeniedNotification;
use App\Notifications\RequestCanvassSummaryDeniedNotification;
use App\Notifications\RequestItemProfilingDeniedNotification;
use App\Notifications\RequestNCPODeniedNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DisapproveApproval extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke($modelType, $model, DisapproveApprovalRequest $request)
    {
        $cacheKey = "disapprove" . $modelType . $model->id . '-'. Auth::user()->id;
        if (Cache::has($cacheKey)) {
            return new JsonResponse(["success" => false, "message" => "Too Many Attempts"], 429);
        }
        return Cache::remember($cacheKey, 5, function () use ($modelType, $model, $request) {
            return $this->disapprove($modelType, $model, $request);
        });
    }

    public function disapprove($modelType, $model, DisapproveApprovalRequest $request)
    {
        $attribute = $request->validated();
        $result = collect($model->updateApproval(['status' => RequestApprovalStatus::DENIED, 'remarks' => $attribute['remarks'], "date_denied" => Carbon::now()]));
        $notificationMap = [
            ApprovalModels::RequestItemProfiling->name => RequestItemProfilingDeniedNotification::class,
            ApprovalModels::RequestBOM->name => RequestBOMDeniedNotification::class,
            ApprovalModels::RequestCanvassSummary->name => RequestCanvassSummaryDeniedNotification::class,
            ApprovalModels::RequestNCPO->name => RequestNCPODeniedNotification::class,
        ];
        if (isset($notificationMap[$modelType])) {
            User::find($model->created_by)->notify(new $notificationMap[$modelType]($request->bearerToken(), $model));
        }
        return new JsonResponse(["success" => $result["success"], "message" => $result['message']], JsonResponse::HTTP_OK);
    }
}
