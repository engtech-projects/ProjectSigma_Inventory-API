<?php

namespace App\Http\Services\ApiServices;

use Illuminate\Support\Facades\Http;

class ProjectService
{
    protected $apiUrl;
    protected $authToken;

    public function __construct($authToken)
    {
        $this->authToken = $authToken;
        $this->apiUrl = config('services.url.projects_api');
    }

    public function getProjects()
    {
        $response = Http::withToken($this->authToken)
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
