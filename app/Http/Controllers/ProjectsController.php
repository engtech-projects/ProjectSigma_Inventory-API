<?php

namespace App\Http\Controllers;

use App\Models\Project;

class ProjectsController extends Controller
{
    public function index()
    {
        $proj = Project::get();

        return response()->json([
            'message' => 'Projects Successfully Fetched.',
            'success' => true,
            'data' => $proj,
        ]);
    }

    public function store()
    {
        return response()->json([
            'message' => 'Moved.',
            'success' => false,
        ], 301)
        ->header('Location', '/api/setup/sync/hrms/employees');
    }
}
