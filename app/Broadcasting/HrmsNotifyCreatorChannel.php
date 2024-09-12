<?php

namespace App\Broadcasting;

use App\Http\Services\HrmsService;
use Notification;

class HrmsNotifyCreatorChannel
{
    public function send($notifiable, Notification $notification)
    {
        $userId = $notifiable->created_by;
        $notif = $notification->toArray($notifiable);
        HrmsService::setNotification($notification->getToken(), $userId, $notif);
    }
}
