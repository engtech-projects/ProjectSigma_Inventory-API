<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidateToken;
use App\Models\Department;

class DepartmentsController extends Controller
{
    public function index()
    {
        $department = Department::get();

        return response()->json([
            'message' => 'Departments Successfully Fetched.',
            'success' => true,
            'data' => $department,
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
