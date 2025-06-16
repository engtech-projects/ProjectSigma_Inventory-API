<?php

namespace App\Http\Services\ApiServices;

use App\Enums\OwnerType;
use App\Models\Project;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProjectMonitoringSecretKeyService
{
    protected $apiUrl;
    protected $authToken;

    public function __construct()
    {
        $this->authToken = config('services.sigma.secret_key');
        $this->apiUrl = config('services.url.projects_api');
        if (empty($this->authToken)) {
            throw new \InvalidArgumentException('SECRET KEY is not configured');
        }
        if (empty($this->apiUrl)) {
            throw new \InvalidArgumentException('Projects API URL is not configured');
        }
    }

    public function syncAll()
    {
        $syncProject = $this->syncProjects();
        return $syncProject;
    }

    public function syncProjects()
    {
        $projects = $this->getAllProjects();
        $warehouses = array_map(fn ($project) => [
            "name" => $project['code'],
            "location" => $project['code'],
            "owner_id" => $project['id'],
            "owner_type" => OwnerType::PROJECT,
        ], $projects);

        $projects = array_map(fn ($project) => [
            "id" => $project['id'],
            "project_monitoring_id" => $project['id'],
            "project_code" => $project['code'],
            "status" => $project['status'],
        ], $projects);

        Project::upsert(
            $projects,
            [
                'id',
                'project_monitoring_id',
            ],
            [
                'project_monitoring_id',
                'project_code',
                'status',
            ]
        );
        Warehouse::upsert(
            $warehouses,
            [
                'id',
                'name',
                'owner_id',
            ],
            [
                'name',
                'location',
                'owner_id',
                'owner_type',
            ]
        );
        return true;
    }

    public function getAllProjects()
    {
        $response = Http::withToken($this->authToken)
            ->withUrlParameters([
                "paginate" => false,
                "sort" => "asc"
            ])
            ->acceptJson()
            ->get($this->apiUrl.'/api/sigma/sync-list/projects');
        if (! $response->successful()) {
            Log::channel("ProjectMonitoringService")->error('Failed to fetch projects from monitoring API', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return [];
        }
        $data = $response->json();
        if (!isset($data['data']) || !is_array($data['data'])) {
            Log::channel("ProjectMonitoringService")->warning('Unexpected response format from projects API', ['response' => $data]);
            return [];
        }
        return $data['data'];
    }
}
