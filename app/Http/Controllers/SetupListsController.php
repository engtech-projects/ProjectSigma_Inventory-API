<?php

namespace App\Http\Controllers;

use App\Http\Resources\DepartmentListResource;
use App\Http\Resources\EmployeeListResource;
use App\Http\Resources\ProjectListResource;
use App\Http\Resources\UsersListResource;
use App\Http\Resources\WarehouseListResource;
use App\Models\SetupDepartments;
use App\Models\SetupEmployees;
use App\Models\SetupProjects;
use App\Models\SetupWarehouses;
use App\Models\User;

class SetupListsController extends Controller
{
    public function getDepartmentList()
    {
        $fetch = SetupDepartments::latest()
        ->paginate(config('app.pagination.per_page'));
        return DepartmentListResource::collection($fetch)
        ->additional([
            'success' => true,
            'message' => 'Departments Successfully Fetched.',
        ]);
    }

    public function getEmployeeList()
    {
        $fetch = SetupEmployees::orderBy('family_name', 'ASC')
        ->paginate(config('app.pagination.per_page'));
        return EmployeeListResource::collection($fetch)
        ->additional([
            'success' => true,
            'message' => 'Employees Successfully Fetched.',
        ]);
    }

    public function getUsersList()
    {
        $fetch = User::with("employee")
            ->latest()
            ->paginate(config('app.pagination.per_page'));
        return UsersListResource::collection($fetch)
        ->additional([
            'success' => true,
            'message' => 'Users Successfully Fetched.',
        ])->response()->getData(true);
    }

    public function getProjectList()
    {
        $fetch = SetupProjects::latest()
        ->paginate(config('app.pagination.per_page'));
        return ProjectListResource::collection($fetch)
        ->additional([
            'success' => true,
            'message' => 'Projects Successfully Fetched.',
        ]);
    }
    public function getWarehouseList()
    {
        $fetch = SetupWarehouses::latest()
        ->paginate(config('app.pagination.per_page'));
        return WarehouseListResource::collection($fetch)
        ->additional([
            'success' => true,
            'message' => 'Projects Successfully Fetched.',
        ]);
    }
}
