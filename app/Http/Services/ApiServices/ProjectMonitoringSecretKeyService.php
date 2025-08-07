<?php

namespace App\Http\Services\ApiServices;

use App\Enums\OwnerType;
use App\Models\SetupProjects;
use App\Models\SetupWarehouses;
use DateTime;
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
        $mappedProjects = array_map(fn ($project) => [
            "id" => $project['id'],
            "project_code" => $project['code'],
            "status" => $project['status'],
            "created_at" => new DateTime($project['created_at']),
            "updated_at" => new DateTime($project['updated_at']),
            "deleted_at" => $project["deleted_at"] ? new DateTime($project['deleted_at']) : null,
        ], $projects);
        $warehouses = array_map(fn ($project) => [
            "owner_id" => $project['id'],
            "owner_type" => OwnerType::PROJECT->value,
            "name" => $project['code'],
            "location" => $project['code'],
            "created_at" => new DateTime($project['created_at']),
            "updated_at" => new DateTime($project['updated_at']),
            "deleted_at" => $project["deleted_at"] ? new DateTime($project['deleted_at']) : null,
        ], $projects);
        SetupProjects::upsert(
            $mappedProjects,
            [
                'id',
            ],
            [
                'project_code',
                'status',
                'created_at',
                'updated_at',
                'deleted_at'
            ]
        );
        SetupWarehouses::upsert(
            $warehouses,
            [
                'owner_id',
                'owner_type',
            ],
            [
                'name',
                'location',
                'created_at',
                'updated_at',
                'deleted_at'
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
            ->get($this->apiUrl . '/api/sigma/sync-list/projects');
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
