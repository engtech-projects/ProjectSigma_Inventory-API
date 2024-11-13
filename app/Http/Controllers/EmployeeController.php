<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidateToken;
use App\Http\Services\HrmsService;
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
    public function store(ValidateToken $request)
    {
        $request = $request->validated();
        $token = $request['token'] ?? null;
        $employees = HrmsService::getEmployees($token);

        if ($employees === false) {
            return response()->json([
                'message' => 'Failed to fetch employees from HRMS API.',
                'success' => false,
            ]);
        }
        foreach ($employees as $emp) {
            Employee::updateOrCreate(
                [
                    'hrms_id' => $emp['id'],
                    'first_name' => $emp['first_name'],
                    'middle_name' => $emp['middle_name'],
                    'family_name' => $emp['family_name'],
                    'name_suffix' => $emp['name_suffix'],
                    'nick_name' => $emp['nick_name'],
                    'gender' => $emp['gender'],
                    'date_of_birth' => $emp['date_of_birth'],
                    'place_of_birth' => $emp['place_of_birth'],
                    'citizenship' => $emp['citizenship'],
                    'blood_type' => $emp['blood_type'],
                    'civil_status' => $emp['civil_status'],
                    'date_of_marriage' => $emp['date_of_marriage'],
                    'telephone_number' => $emp['telephone_number'],
                    'mobile_number' => $emp['mobile_number'],
                    'email' => $emp['email'],
                    'religion' => $emp['religion'],
                    'weight' => $emp['weight'],
                    'height' => $emp['height'],
                ]
            );
        }
        return response()->json([
            'message' => 'Employees synchronized successfully.',
            'success' => true,
            'data' => $employees,
        ]);
    }
}
