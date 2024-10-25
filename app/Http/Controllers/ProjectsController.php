<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidateToken;
use App\Http\Services\HrmsService;
use App\Models\Project;

class ProjectsController extends Controller
{
    public function store(ValidateToken $request)
    {
        $request = $request->validated();
        $token = $request['token'] ?? null;
        $projects = HrmsService::getProjects($token);

        // dd($projects);

        if ($projects === false) {
            return response()->json([
                'message' => 'Failed to fetch projects from HRMS API.',
                'success' => false,
            ]);
        }
        foreach ($projects as $proj) {
            Project::updateOrCreate(
                [
                    'hrms_id' => $proj['id'],
                    'project_monitoring_id' => $proj['project_monitoring_id'],
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
