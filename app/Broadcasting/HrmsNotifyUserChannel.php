<?php

namespace App\Broadcasting;

use App\Http\Services\ApiServices\HrmsService;
use Notification;

class HrmsNotifyUserChannel
{
    public function send($notifiable, Notification $notification): void
    {
        $userId = $notifiable->id;
        $notif = $notification->toArray($notifiable);
        HrmsService::setNotification($notification->getToken(), $userId, $notif);
    }
}
