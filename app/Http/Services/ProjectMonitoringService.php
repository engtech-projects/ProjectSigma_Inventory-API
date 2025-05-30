<?php

namespace App\Http\Services;

use App\Enums\OwnerType;
use App\Models\Project;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Http;

class ProjectMonitoringService
{
    protected $apiUrl;
    protected $authToken;

    public function __construct()
    {
        $this->authToken = config('services.sigma.secret_key');
        $this->apiUrl = config('services.url.projects_api');
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
            ->get($this->apiUrl.'/api/projects');
        if (! $response->successful()) {
            return [];
        }
        $data = $response->json();
        return is_array($data) ? $data : [];
    }
}
