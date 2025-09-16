<?php

namespace App\Http\Controllers\Actions\Approvals;

use App\Enums\ApprovalModels;
use Illuminate\Http\JsonResponse;
use App\Enums\RequestApprovalStatus;
use App\Http\Controllers\Controller;
use App\Notifications\RequestBOMApprovedNotification;
use App\Notifications\RequestBOMForApprovalNotification;
use App\Notifications\RequestCanvassSummaryApprovalNotification;
use App\Notifications\RequestCanvassSummaryApprovedNotification;
use App\Notifications\RequestNcpoApprovedNotification;
use App\Notifications\RequestNcpoForApprovalNotification;
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
        return Cache::remember($cacheKey, 5, function () use ($modelType, $model, $request) {
            return $this->approve($modelType, $model, $request);
        });
    }
    public function approve($modelType, $model, Request $request)
    {
        $result = $model->updateApproval(['status' => RequestApprovalStatus::APPROVED, "date_approved" => Carbon::now()]);
        $nextApproval = $model->getNextPendingApproval();
        if ($nextApproval) {
            $notificationMap = [
                ApprovalModels::RequestSupplier->name => RequestSupplierForApprovalNotification::class,
                ApprovalModels::RequestBOM->name => RequestBOMForApprovalNotification::class,
                ApprovalModels::RequestCanvassSummary->name => RequestCanvassSummaryApprovalNotification::class,
                ApprovalModels::RequestNcpo->name => RequestNcpoForApprovalNotification::class,
            ];
            if (isset($notificationMap[$modelType])) {
                $model->notifyNextApprover($notificationMap[$modelType]);
            }
        } else {
            $notificationMap = [
                ApprovalModels::RequestSupplier->name => RequestSupplierApprovedNotification::class,
                ApprovalModels::RequestBOM->name => RequestBOMApprovedNotification::class,
                ApprovalModels::RequestCanvassSummary->name => RequestCanvassSummaryApprovedNotification::class,
                ApprovalModels::RequestNcpo->name => RequestNcpoApprovedNotification::class,
            ];
            if (isset($notificationMap[$modelType])) {
                $model->notifyCreator($notificationMap[$modelType]);
            }
        }
        return new JsonResponse(["success" => $result["success"], "message" => $result['message']], $result["status_code"]);
    }
}
