<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Http;

class HrmsService
{
    public static function setNotification($token, $userid, $notificationData)
    {
        if(gettype($notificationData) == "array"){
            $notificationData = json_encode($notificationData);
        }
        $response = Http::withToken(token: $token)
            ->acceptJson()
            ->withBody($notificationData)
            ->post(config('services.url.hrms_api_url')."/api/notifications/services-notify/{$userid}");
        if (!$response->successful()) {
            return false;
        }

        // return $response->json();
    }

    public static function formatApprovals($token, $approvals)
    {
        $response = Http::withToken($token)
            ->acceptJson()
            ->withQueryParameters(parameters: $approvals)
            ->get(config('services.url.hrms_api_url')."/api/services/format-approvals");
        if (!$response->successful()) {
            return [];
        }
        return $response->json("data");
    }
}
