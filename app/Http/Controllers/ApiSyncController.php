<?php

namespace App\Http\Controllers;

use App\Http\Services\ProjectMonitoringService;
use App\Http\Services\HrmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiSyncController extends Controller
{
    public function syncAll(Request $request)
    {
        $authToken = $request->bearerToken();
        DB::transaction(function () use ($authToken) {
            $projectService = new ProjectMonitoringService($authToken);
            $hrmsService = new HrmsService($authToken);
            if (!$projectService->syncAll() && !$hrmsService->syncAll()) {
                throw new \Exception("Project monitoring sync failed.");
            }
        });
        return response()->json([
            'message' => 'Successfully synced with api services.',
            'success' => true,
        ]);
    }

    public function syncAllProjectMonitoring(Request $request)
    {
        $authToken = $request->bearerToken();
        DB::transaction(function () use ($authToken) {
            $projectService = new ProjectMonitoringService($authToken);
            if (!$projectService->syncAll()) {
                throw new \Exception("Project monitoring sync failed.");
            }
        });
        return response()->json([
            'message' => 'Successfully synced with Project Monitoring api service.',
            'success' => true,
        ]);
    }

    public function syncAllHrms(Request $request)
    {
        $authToken = $request->bearerToken();
        DB::transaction(function () use ($authToken) {
            $hrmsService = new HrmsService($authToken);
            if (!$hrmsService->syncAll()) {
                throw new \Exception("Project monitoring sync failed.");
            }
        });
        return response()->json([
            'message' => 'Successfully synced with Project Monitoring api service.',
            'success' => true,
        ]);
    }

    public function syncProjects(Request $request)
    {
        $authToken = $request->bearerToken();
        DB::transaction(function () use ($authToken) {
            $projectService = new ProjectMonitoringService($authToken);
            if (!$projectService->syncProjects()) {
                throw new \Exception("Project monitoring sync failed.");
            }
        });
        return response()->json([
            'message' => 'Successfully synced all projects.',
            'success' => true,
        ]);
    }

    public function syncEmployees(Request $request)
    {
        $authToken = $request->bearerToken();
        DB::transaction(function () use ($authToken) {
            $hrmsService = new HrmsService($authToken);
            if (!$hrmsService->syncEmployees()) {
                throw new \Exception("Employee sync failed.");
            }
        });
        return response()->json([
            'message' => 'Successfully synced all Employees.',
            'success' => true,
        ]);
    }

    public function syncDepartments(Request $request)
    {
        $authToken = $request->bearerToken();
        DB::transaction(function () use ($authToken) {
            $hrmsService = new HrmsService($authToken);
            if (!$hrmsService->syncDepartments()) {
                throw new \Exception("Departments sync failed.");
            }
        });
        return response()->json([
            'message' => 'Successfully synced all Departments.',
            'success' => true,
        ]);
    }

    public function syncUsers(Request $request)
    {
        $authToken = $request->bearerToken();
        DB::transaction(function () use ($authToken) {
            $hrmsService = new HrmsService($authToken);
            if (!$hrmsService->syncUsers()) {
                throw new \Exception("Users sync failed.");
            }
        });
        return response()->json([
            'message' => 'Successfully synced all Users.',
            'success' => true,
        ]);
    }


}
