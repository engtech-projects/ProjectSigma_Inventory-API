<?php

namespace App\Http\Controllers;

use App\Http\Resources\DepartmentListResource;
use App\Http\Resources\EmployeeListResource;
use App\Http\Resources\ProjectListResource;
use App\Http\Resources\UsersListResource;
use App\Models\Employee;
use App\Models\Project;
use App\Models\SetupDepartments;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class SetupListsController extends Controller
{
    public function getDepartmentList()
    {
        $fetch = SetupDepartments::orderBy('created_at', 'DESC')
            ->paginate();
        return DepartmentListResource::collection($fetch)
            ->additional([
                'success' => true,
                'message' => 'Departments Successfully Fetched.',
            ]);
    }

    public function getEmployeeList()
    {
        $fetch = Employee::orderBy('created_at', 'DESC')
            ->paginate();
        $requestResources = EmployeeListResource::collection($fetch)->response()->getData(true);
        return new JsonResponse([
            'success' => true,
            'message' => 'Employee Successfully Fetched.',
            'data' => $requestResources
        ]);
    }

    public function getUsersList()
    {
        $fetch = User::orderBy('created_at', 'DESC')
            ->paginate();
        $requestResources = UsersListResource::collection($fetch)->response()->getData(true);
        return new JsonResponse([
            'success' => true,
            'message' => 'Users Successfully Fetched.',
            'data' => $requestResources
        ]);
    }

    public function getProjectList()
    {
        $fetch = Project::orderBy('created_at', 'DESC')
            ->paginate();
        $requestResources = ProjectListResource::collection($fetch)->response()->getData(true);
        return new JsonResponse([
            'success' => true,
            'message' => 'Projects Successfully Fetched.',
            'data' => $requestResources
        ]);
    }
}
