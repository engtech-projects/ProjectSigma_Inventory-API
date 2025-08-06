<?php

namespace App\Http\Controllers\Actions\Approvals;

use App\Enums\ApprovalModels;
use Illuminate\Http\JsonResponse;
use App\Enums\RequestApprovalStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\RequestBOMApprovedNotification;
use App\Notifications\RequestBOMForApprovalNotification;
use App\Notifications\RequestSupplierApprovedNotification;
use App\Notifications\RequestSupplierForApprovalNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ApproveApproval extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke($modelType, $model, Request $request)
    {
        $cacheKey = "approve" . $modelType . $model->id . '-'. Auth::user()->id;
        if (Cache::has($cacheKey)) {
            return new JsonResponse(["success" => false, "message" => "Too Many Attempts"], 429);
        }
        return Cache::remember($cacheKey, 5, function () use ($modelType, $model) {
            return $this->approve($modelType, $model);
        });
    }
    public function approve($modelType, $model)
    {
        $result = $model->updateApproval(['status' => RequestApprovalStatus::APPROVED, "date_approved" => Carbon::now()]);
        $nextApproval = $model->getNextPendingApproval();
        if ($nextApproval) {
            $nextApprovalUser = $nextApproval["user_id"];
            $notificationMap = [
                ApprovalModels::RequestSupplier->name => RequestSupplierForApprovalNotification::class,
                ApprovalModels::RequestBOM->name => RequestBOMForApprovalNotification::class,
            ];
            if (isset($notificationMap[$modelType])) {
                User::find($nextApprovalUser)->notify(new $notificationMap[$modelType]($model));
            }
        } else {
            $notificationMap = [
                ApprovalModels::RequestSupplier->name => RequestSupplierApprovedNotification::class,
                ApprovalModels::RequestBOM->name => RequestBOMApprovedNotification::class,
            ];
            if (isset($notificationMap[$modelType])) {
                User::find($model->created_by)->notify(new $notificationMap[$modelType]($model));
            }
        }
        return new JsonResponse(["success" => $result["success"], "message" => $result['message']], $result["status_code"]);
    }
}
