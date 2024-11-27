<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidateToken;
use App\Http\Services\ProjectService;
use App\Models\Project;

class ProjectsController extends Controller
{
    public function store(ValidateToken $request)
    {
        $request = $request->validated();
        $token = $request['token'] ?? null;
        $projects = ProjectService::getProjects($token);

        if ($projects === false) {
            return response()->json([
                'message' => 'Failed to fetch projects from Projects API.',
                'success' => false,
            ]);
        }
        foreach ($projects as $proj) {
            Project::updateOrCreate(
                [
                    'project_monitoring_id' => $proj['id'],
                    'project_code' => $proj['project_code'],
                    'status' => $proj['status'],
                ]
            );
        }
        return response()->json([
            'message' => 'Projects synchronized successfully.',
            'success' => true,
            'data' => $projects,
        ]);
    }
}
