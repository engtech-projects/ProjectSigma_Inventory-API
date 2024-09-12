<?php

namespace App\Broadcasting;

use App\Http\Services\HrmsService;
use Notification;

class HrmsNotifyNextApproverChannel
{
    public function send($notifiable, Notification $notification)
    {
        $userId = $notifiable->getNextPendingApproval()['user_id'];
        $notif = $notification->toArray($notifiable);
        HrmsService::setNotification($notification->getToken(), $userId, $notif);
    }
}
