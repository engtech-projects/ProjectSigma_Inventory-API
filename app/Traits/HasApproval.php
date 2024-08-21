<?php

namespace App\Traits;

use Illuminate\Support\Carbon;
use App\Enums\RequestApprovalStatus;
use App\Enums\RequestStatusType;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

trait HasApproval
{
    public function completeRequestStatus()
    {
        $this->request_status = RequestApprovalStatus::APPROVED;
        $this->save();
        $this->refresh();
    }
    public function denyRequestStatus()
    {
        $this->request_status = RequestApprovalStatus::DENIED;
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
    public function scopeAuthUserPending(Builder $query): void
    {
        $query->whereJsonLength('approvals', '>', 0)
            ->whereJsonContains('approvals', ['user_id' => auth()->user()->id, 'status' => RequestApprovalStatus::PENDING]);
    }

    public function getUserPendingApproval($userId)
    {
        return collect($this->approvals)->where('user_id', $userId)
            ->where('status', RequestApprovalStatus::PENDING);
    }
    public function getNextPendingApproval()
    {
        if($this->request_status != RequestStatusType::PENDING->value) {
            return null;
        }
        return collect($this->approvals)->where('status', RequestApprovalStatus::PENDING)->first();
    }

    public function setNewApproval($approvalToUpdate, $data)
    {
        $manpowerRequestApproval = collect($this->approvals)->map(function ($item, int $key) use ($approvalToUpdate, $data) {
            if ($key === $approvalToUpdate) {

                $item['status'] = $data['status'];
                if ($data["status"] === RequestApprovalStatus::DENIED) {
                    $data['date_denied'] = Carbon::now()->format('Y-m-d');
                } else {
                    $data['date_approved'] = Carbon::now()->format('Y-m-d');
                }
                $item['remarks'] = array_key_exists("remarks", $data) ? $data["remarks"] : $item["remarks"];
            }
            return $item;
        })->all();
        return $manpowerRequestApproval;
    }
    public function updateApproval(?array $data)
    {
        $userApproval = $this->getUserPendingApproval(auth()->user()->id)->first();
        $nextApproval = $this->getNextPendingApproval();

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
        // CHECK IF THE CURRENT USER HAS PENDING APPROVAL AND SET RESPONSE DATA
        if (!empty($nextApproval) && $nextApproval['user_id'] != auth()->user()->id) {
            return [
                "approvals" => $this->approvals,
                'success' => false,
                "status_code" => JsonResponse::HTTP_FORBIDDEN,
                "message" => "Failed to {$data['status']}. Your approval is for later or already done.",
            ];
        }
        DB::beginTransaction();
        // SET NEW MAN POWER REQUEST APPROVAL FOR RESOURCE UPDATE
        $approvalToUpdate = collect($this->approvals)->search($userApproval);
        $newApproval = $this->setNewApproval($approvalToUpdate, $data);
        // SAVE NEW RESOURCE FOR MANPOWER REQUEST
        $this->approvals = $newApproval;
        $this->save();
        if (RequestApprovalStatus::DENIED === $data['status']) {
            $this->denyRequestStatus();
        }
        // IF LAST APPROVAL complete Request Status
        if (collect($newApproval)->last()['status'] === RequestApprovalStatus::APPROVED) {
            $this->completeRequestStatus();
        }
        DB::commit();
        return [
            "approvals" => $newApproval,
            'success' => true,
            "status_code" => JsonResponse::HTTP_OK,
            "message" => $data['status'] === RequestApprovalStatus::APPROVED ? "Successfully approved." : "Successfully denied.",
        ];
    }
}
