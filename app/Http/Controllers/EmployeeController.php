<?php

namespace App\Http\Controllers;

use App\Models\Employee;

class EmployeeController extends Controller
{
    public function index()
    {
        $request = Employee::get();

        return response()->json([
            'message' => 'Employees Successfully Fetched.',
            'success' => true,
            'data' => $request,
        ]);
    }
    public function store()
    {
        return response()->json([
            'message' => 'Moved.',
            'success' => false,
        ], 302);
    }
}
