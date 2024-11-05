<?php

namespace App\Traits;

use Illuminate\Support\Carbon;
use App\Enums\RequestStatuses;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasApproval
{
    /**
     * ==================================================
     * MODEL ATTRIBUTES
     * ==================================================
     */
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


    /**
    * ==================================================
    * MODEL RELATIONSHIPS
    * ==================================================
    */
    public function created_by_user(): BelongsTo
    {
        return $this->belongsTo(User::class, "created_by", "id");
    }

    /**
    * ==================================================
    * LOCAL SCOPES
    * ==================================================
    */
    public function scopeMyApprovals(Builder $query): void
    {
        $userId = auth()->user()->id;
        $query->where('request_status', RequestStatuses::PENDING)
            ->whereJsonContains('approvals', ['user_id' => $userId])
            ->whereJsonContains('approvals', ['status' => RequestStatuses::PENDING]);
    }

    public function scopeAuthUserPending(Builder $query): void
    {
        $query->whereJsonLength('approvals', '>', 0)
            ->whereJsonContains('approvals', ['user_id' => auth()->user()->id, 'status' => RequestStatuses::PENDING]);
    }
    public function scopeIsApproved(Builder $query): void
    {
        $query->where('request_status', RequestStatuses::APPROVED);
    }


    /**
    * ==================================================
    * DYNAMIC SCOPES
    * ==================================================
    */
    public function completeRequestStatus()
    {
        $this->request_status = RequestStatuses::APPROVED;
        $this->save();
        $this->refresh();
    }
    public function denyRequestStatus()
    {
        $this->request_status = RequestStatuses::DENIED;
        $this->save();
        $this->refresh();
    }
    public function cancelRequestStatus()
    {
        $this->request_status = RequestStatuses::CANCELLED;
        $this->save();
        $this->refresh();
    }
    public function voidRequestStatus()
    {
        $this->request_status = RequestStatuses::VOIDED;
        $this->save();
        $this->refresh();
    }

    public function getUserPendingApproval($userId)
    {
        return collect($this->approvals)->where('user_id', $userId)
            ->where('status', RequestStatuses::PENDING);
    }
    public function getNextPendingApproval()
    {
        if($this->request_status != RequestStatuses::PENDING) {
            return null;
        }
        return collect($this->approvals)->where('status', RequestStatuses::PENDING)->first();
    }

    public function setNewApproval($approvalToUpdate, $data)
    {
        $manpowerRequestApproval = collect($this->approvals)->map(function ($item, int $key) use ($approvalToUpdate, $data) {
            if ($key === $approvalToUpdate) {

                $item['status'] = $data['status'];
                if ($data["status"] === RequestStatuses::DENIED) {
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
        // UPDATE CURRENT APPROVAL TO DENIED/APPROVED/CANCELLED/VOIDED
        switch ($data['status']) {
            case RequestStatuses::DENIED:
                $this->denyCurrentApproval($data["remarks"]);
                $message = "Successfully denied.";
                break;
            case RequestStatuses::CANCELLED:
                $this->cancelCurrentApproval($data["remarks"]);
                $message = "Successfully cancelled.";
                $data['date_cancelled'] = Carbon::now()->format('F j, Y h:i A');
                break;
            case RequestStatuses::VOIDED:
                $this->voidCurrentApproval($data["remarks"]);
                $message = "Successfully voided.";
                break;
            default:
                $this->approveCurrentApproval();
                $message = "Successfully approved.";
        }
        DB::commit();
        return [
            "approvals" => $this->approvals,
            'success' => true,
            "status_code" => JsonResponse::HTTP_OK,
            "message" => $message,
        ];
    }
    // public function updateApproval(?array $data)
    // {
    //     // CHECK IF MANPOWER REQUEST ALREADY DISAPPROVED AND SET RESPONSE DATA
    //     if ($this->requestStatusEnded()) {
    //         return [
    //             "approvals" => $this->approvals,
    //             'success' => false,
    //             "status_code" => JsonResponse::HTTP_FORBIDDEN,
    //             "message" => "The request was already ended.",
    //         ];
    //     }
    //     // CHECK IF MANPOWER REQUEST ALREADY COMPLETED AND SET RESPONSE DATA
    //     if ($this->requestStatusCompleted()) {
    //         return [
    //             "approvals" => $this->approvals,
    //             'success' => false,
    //             "status_code" => JsonResponse::HTTP_FORBIDDEN,
    //             "message" => "The request was already completed.",
    //         ];
    //     }
    //     $currentApproval = $this->getNextPendingApproval();
    //     // CHECK IF THERE IS A CURRENT APPROVAL AND IF IS FOR THE LOGGED IN USER
    //     if (!empty($currentApproval) && $currentApproval['user_id'] != auth()->user()->id) {
    //         return [
    //             "approvals" => $this->approvals,
    //             'success' => false,
    //             "status_code" => JsonResponse::HTTP_FORBIDDEN,
    //             "message" => "Failed to {$data['status']}. Your approval is for later or already done.",
    //         ];
    //     }
    //     DB::beginTransaction();
    //     // UPDATE CURRENT APPROVAL TO DENIED/APPROVED/CANCELLED/VOIDED
    //     switch ($data['status']) {
    //         case RequestStatuses::DENIED:
    //             $this->denyCurrentApproval($data["remarks"]);
    //             $message = "Successfully denied.";
    //             break;
    //         case RequestStatuses::CANCELLED:
    //             $this->cancelCurrentApproval($data["remarks"]);
    //             $message = "Successfully cancelled.";
    //             break;
    //         case RequestStatuses::VOIDED:
    //             $this->voidCurrentApproval($data["remarks"]);
    //             $message = "Successfully voided.";
    //             break;
    //         default:
    //             $this->approveCurrentApproval();
    //             $message = "Successfully approved.";
    //     }
    //     DB::commit();
    //     return [
    //         "approvals" => $currentApproval,
    //         'success' => true,
    //         "status_code" => JsonResponse::HTTP_OK,
    //         "message" => $message,
    //     ];
    // }

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
        if (collect($this->approvals)->last()['status'] === RequestStatuses::APPROVED) {
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
    public function cancelCurrentApproval($remarks)
    {
        $currentApproval = $this->getNextPendingApproval();
        $currentApprovalIndex = collect($this->approvals)->search($currentApproval);
        $this->approvals = collect($this->approvals)->map(function ($approval, $index) use ($currentApprovalIndex, $remarks) {
            if ($index === $currentApprovalIndex) {
                $approval["status"] = RequestStatuses::CANCELLED;
                $approval["date_cancelled"] = Carbon::now()->format('F j, Y h:i A');
                $approval["remarks"] = $remarks;
            }
            return $approval;
        });
        $this->save();
        $this->cancelRequestStatus();
    }
    
    // public function voidCurrentApproval($remarks)
    // {
    //     // USE THIS FUNCTION IF SURE TO VOID CURRENT APPROVAL AND VERIFIED IF CURRENT APPROVAL IS CURRENT USER
    //     $currentApproval = $this->getNextPendingApproval();
    //     $currentApprovalIndex = collect($this->approvals)->search($currentApproval);
    //     $this->approvals = collect($this->approvals)->map(function ($approval, $index) use ($currentApprovalIndex, $remarks) {
    //         if ($index === $currentApprovalIndex) {
    //             $approval["status"] = RequestStatuses::VOIDED;
    //             $approval["date_voided"] = Carbon::now()->format('F j, Y h:i A');
    //             $approval["remarks"] = $remarks;
    //         }
    //         return $approval;
    //     })->all();
    //     $this->save();
    //     $this->voidRequestStatus();
    // }
}
