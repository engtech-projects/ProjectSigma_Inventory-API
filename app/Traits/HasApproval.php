<?php

namespace App\Traits;

use Illuminate\Support\Carbon;
use App\Enums\RequestStatuses;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

trait HasApproval
{
    /**
     * ==================================================
     * MODEL RELATIONSHIPS
     * ==================================================
     */
    public function created_by_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * ==================================================
     * MODEL ATTRIBUTES
     * ==================================================
     */
    public function getCreatedByUserNameAttribute()
    {
        return $this->created_by_user->employee?->fullname_first ?? $this->created_by_user->name;
    }

    /**
     * ==================================================
     * STATIC SCOPES
     * ==================================================
     */
    public function scopeRequestStatusPending(Builder $query): void
    {
        $query->where('request_status', RequestStatuses::PENDING);
    }
    public function scopeAuthUserPending(Builder $query): void
    {
        $query->whereJsonLength('approvals', '>', 0)
            ->whereJsonContains('approvals', ['user_id' => auth()->user()->id, 'status' => RequestStatuses::PENDING]);
    }
    public function scopeAuthUserNextApproval(Builder $query): void
    {
        $userId = auth()->user()->id;
        $query->whereRaw("
            JSON_UNQUOTE(JSON_SEARCH(approvals, 'one', 'Pending', NULL, '$[*].status')) IS NOT NULL AND
            JSON_UNQUOTE(JSON_EXTRACT(approvals, JSON_UNQUOTE(JSON_SEARCH(approvals, 'one', 'Pending', NULL, '$[*].status')))) = 'Pending' AND
            JSON_UNQUOTE(JSON_EXTRACT(approvals, REPLACE(JSON_UNQUOTE(JSON_SEARCH(approvals, 'one', 'Pending', NULL, '$[*].status')), '.status', '.user_id'))) = ?
        ", [$userId]);
    }
    public function scopeIsPending(Builder $query): void
    {
        $query->where('request_status', RequestStatuses::PENDING->value);
    }
    public function scopeIsApproved(Builder $query): void
    {
        $query->where('request_status', RequestStatuses::APPROVED->value);
    }
    public function scopeIsDenied(Builder $query): void
    {
        $query->where('request_status', RequestStatuses::DENIED->value);
    }
    public function scopeMyRequests(Builder $query): void
    {
        $query->where('created_by', auth()->user()->id);
    }
    public function scopeMyApprovals(Builder $query): void
    {
        $query->requestStatusPending()->authUserNextApproval();
    }

    /**
     * ==================================================
     * DYNAMIC SCOPES
     * ==================================================
     */

    /**
     * ==================================================
     * MODEL FUNCTIONS
     * ==================================================
     */
    public function completeRequestStatus()
    {
        $this->request_status = RequestStatuses::APPROVED->value;
        $this->save();
        $this->refresh();
    }
    public function denyRequestStatus()
    {
        $this->request_status = RequestStatuses::DENIED->value;
        $this->save();
        $this->refresh();
    }

    public function setRequestStatus(?string $newStatus)
    {
    }
    public function requestStatusCompleted(): bool
    {
        return false;
    }
    public function requestStatusEnded(): bool
    {
        return false;
    }
    public function getUserPendingApproval($userId)
    {
        return collect($this->approvals)->where('user_id', $userId)
            ->where('status', RequestStatuses::PENDING);
    }
    public function getNextPendingApproval()
    {
        if ($this->request_status != RequestApprovalStatus::PENDING) {
            return null;
        }
        return collect($this->approvals)->where('status', RequestStatuses::PENDING->value)->first();
    }
    public function approveCurrentApproval()
    {
        // USE THIS FUNCTION IF SURE TO APPROVE CURRENT APPROVAL AND VERIFIED IF CURRENT APPROVAL IS CURRENT USER
        $currentApproval = $this->getNextPendingApproval();
        $currentApprovalIndex = collect($this->approvals)->search($currentApproval);
        $this->approvals = collect($this->approvals)->map(function ($approval, $index) use ($currentApprovalIndex) {
            if ($index === $currentApprovalIndex) {
                $approval["status"] = RequestStatuses::APPROVED;
                $approval["date_approved"] = Carbon::now()->format('F j, Y h:i A');
            }
            return $approval;
        });
        $this->save();
        $this->refresh();
        if (collect($this->approvals)->last()['status'] === RequestStatuses::APPROVED->value) {
            $this->completeRequestStatus();
        }
    }
    public function denyCurrentApproval($remarks)
    {
        // USE THIS FUNCTION IF SURE TO DENY CURRENT APPROVAL AND VERIFIED IF CURRENT APPROVAL IS CURRENT USER
        $currentApproval = $this->getNextPendingApproval();
        $currentApprovalIndex = collect($this->approvals)->search($currentApproval);
        $this->approvals = collect($this->approvals)->map(function ($approval, $index) use ($currentApprovalIndex, $remarks) {
            if ($index === $currentApprovalIndex) {
                $approval["status"] = RequestStatuses::DENIED;
                $approval["date_denied"] = Carbon::now()->format('F j, Y h:i A');
                $approval["remarks"] = $remarks;
            }
            return $approval;
        });
        $this->save();
        $this->denyRequestStatus();
    }

    public function updateApproval(?array $data)
    {
        // CHECK IF MANPOWER REQUEST ALREADY DISAPPROVED AND SET RESPONSE DATA
        if ($this->requestStatusEnded()) {
            return [
                "approvals" => $this->approvals,
                'success' => false,
                "status_code" => JsonResponse::HTTP_FORBIDDEN,
                "message" => "The request was already ended.",
            ];
        }
        // CHECK IF MANPOWER REQUEST ALREADY COMPLETED AND SET RESPONSE DATA
        if ($this->requestStatusCompleted()) {
            return [
                "approvals" => $this->approvals,
                'success' => false,
                "status_code" => JsonResponse::HTTP_FORBIDDEN,
                "message" => "The request was already completed.",
            ];
        }
        $currentApproval = $this->getNextPendingApproval();
        // CHECK IF THERE IS A CURRENT APPROVAL AND IF IS FOR THE LOGGED IN USER
        if (!empty($currentApproval) && $currentApproval['user_id'] != auth()->user()->id) {
            return [
                "approvals" => $this->approvals,
                'success' => false,
                "status_code" => JsonResponse::HTTP_FORBIDDEN,
                "message" => "Failed to {$data['status']}. Your approval is for later or already done.",
            ];
        }
        DB::beginTransaction();
        if ($data['status'] === RequestStatuses::DENIED->value) {
            $this->denyCurrentApproval($data["remarks"]);
        } else {
            $this->approveCurrentApproval();
        }
        DB::commit();
        return [
            "approvals" => $currentApproval,
            'success' => true,
            "status_code" => JsonResponse::HTTP_OK,
            "message" => $data['status'] === RequestStatuses::APPROVED->value ? "Successfully approved." : "Successfully denied.",
        ];
    }
}
