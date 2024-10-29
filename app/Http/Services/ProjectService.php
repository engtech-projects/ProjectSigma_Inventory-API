<?php

namespace App\Http\Services;

use App\Models\Department;
use Illuminate\Support\Facades\Http;

class ProjectService
{

    public static function getProjects($token)
    {
        $response = Http::withToken($token)
            ->acceptJson()
            ->get(
                config('services.url.projects_api') . '/api/projects'
            );

        if (!$response->successful()) {
            return false;
        }
        return $response->json("data");
        // $projects = $response->json("data");
        // $filteredProjects = array_map(function($project) {
        //     unset($project['project_members']);
        //     return $project;
        // }, $projects);

        // return $filteredProjects;
    }
}
