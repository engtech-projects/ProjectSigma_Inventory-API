<?php

namespace App\Http\Controllers;

use App\Http\Resources\DepartmentListResource;
use App\Http\Resources\EmployeeListResource;
use App\Http\Resources\ProjectListResource;
use App\Http\Resources\UsersListResource;
use App\Models\Project;
use App\Models\SetupDepartments;
use App\Models\SetupEmployees;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class SetupListsController extends Controller
{
    public function getDepartmentList()
    {
        $fetch = SetupDepartments::orderBy('created_at', 'DESC')
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
            ->orderBy('created_at', 'DESC')
            ->paginate(config('app.pagination.per_page'));
        return UsersListResource::collection($fetch)
        ->additional([
            'success' => true,
            'message' => 'Users Successfully Fetched.',
        ])->response()->getData(true);
    }

    public function getProjectList()
    {
        $fetch = Project::orderBy('created_at', 'DESC')
            ->paginate(config('app.pagination.per_page'));
        $requestResources = ProjectListResource::collection($fetch)->response()->getData(true);
        return new JsonResponse([
            'success' => true,
            'message' => 'Projects Successfully Fetched.',
            'data' => $requestResources
        ]);
    }
}
