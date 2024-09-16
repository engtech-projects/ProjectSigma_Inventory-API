<?php

namespace App\Broadcasting;

use App\Http\Services\HrmsService;
use App\Notifications\RequestItemProfilingForApprovalNotification;

class HrmsNotifyNextApproverChannel
{
    public function send($notifiable, RequestItemProfilingForApprovalNotification $notification): void
    {
        $userId = $notifiable->getNextPendingApproval()['user_id'];
        $notif = $notification->toArray($notifiable);
        HrmsService::setNotification($notification->getToken(), $userId, $notif);
    }
}
