<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Http;

class ProjectService
{
    public static function getProjects($token)
    {
        $response = Http::withToken($token)
            ->acceptJson()
            ->get(
                config('services.url.project_api') . '/api/projects'
            );
        if (!$response->successful()) {
            return false;
        }
        return $response->json("data");
    }
}
