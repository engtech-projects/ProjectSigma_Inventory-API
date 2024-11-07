<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidateToken;
use App\Http\Services\HrmsService;
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
    public function store(ValidateToken $request)
    {
        $request = $request->validated();
        $token = $request['token'] ?? null;
        $departments = HrmsService::getDepartments($token);

        if ($departments === false) {
            return response()->json([
                'message' => 'Failed to fetch departments from HRMS API.',
                'success' => false,
            ]);
        }
        foreach ($departments as $dept) {
            Department::updateOrCreate(
                [
                    'hrms_id' => $dept['id'],
                    'department_name' => $dept['department_name']
                ]
            );
        }
        return response()->json([
            'message' => 'Departments synchronized successfully.',
            'success' => true,
            'data' => $departments,
        ]);
    }
}
