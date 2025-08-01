<?php

namespace App\Http\Services\ApiServices;

use Illuminate\Support\Facades\Http;

class HrmsService
{
    protected $apiUrl;
    protected $authToken;

    public function __construct($authToken)
    {
        $this->apiUrl = config('services.url.hrms_api_url');
        $this->authToken = $authToken;
        if (empty($this->apiUrl)) {
            throw new \InvalidArgumentException('HRMS API URL is not configured');
        }
    }

    public static function setNotification($token, $userid, $notificationData)
    {
        if (is_array($notificationData)) {
            $notificationData = json_encode($notificationData);
        }
        $response = Http::withToken(token: $token)
            ->acceptJson()
            ->withBody($notificationData)
            ->post(config('services.url.hrms_api_url') . "/api/notifications/services-notify/{$userid}");
        if (!$response->successful()) {
            return false;
        }
        return true;
    }

    public static function formatApprovals($token, $approvals)
    {
        $response = Http::withToken($token)
            ->acceptJson()
            ->withQueryParameters($approvals)
            ->get(config('services.url.hrms_api_url') . "/api/services/format-approvals");
        if (!$response->successful()) {
            return $approvals;
        }
        return $response->json()["data"];
    }

    public static function getEmployeeDetails($token, $user_ids)
    {
        $response = Http::withToken($token)
            ->acceptJson()
            ->get(config('services.url.hrms_api_url') . '/api/services/user-employees', [
                'user_ids' => $user_ids
            ]);

        if (!$response->successful()) {
            return false;
        }

        return $response->json("data");
    }
}
