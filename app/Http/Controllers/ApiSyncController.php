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
        DB::transaction(function () {
            $projectService = new ProjectMonitoringService();
            $hrmsService = new HrmsService();
            if (!($projectService->syncAll() || $hrmsService->syncAll())) {
                throw new \Exception("Sync with API services failed.");
            }
        });

        return response()->json([
            'message' => 'Successfully synced with API services.',
            'success' => true,
        ]);
    }

    public function syncAllProjectMonitoring(Request $request)
    {
        DB::transaction(function () {
            $projectService = new ProjectMonitoringService();
            if (!$projectService->syncAll()) {
                throw new \Exception("Project monitoring sync failed.");
            }
        });

        return response()->json([
            'message' => 'Successfully synced with Project Monitoring API service.',
            'success' => true,
        ]);
    }

    public function syncAllHrms(Request $request)
    {
        DB::transaction(function () {
            $hrmsService = new HrmsService();
            if (!$hrmsService->syncAll()) {
                throw new \Exception("HRMS sync failed.");
            }
        });

        return response()->json([
            'message' => 'Successfully synced with HRMS API service.',
            'success' => true,
        ]);
    }

    public function syncProjects(Request $request)
    {
        DB::transaction(function () {
            $projectService = new ProjectMonitoringService();
            if (!$projectService->syncProjects()) {
                throw new \Exception("Project sync failed.");
            }
        });

        return response()->json([
            'message' => 'Successfully synced all projects.',
            'success' => true,
        ]);
    }

    public function syncEmployees(Request $request)
    {
        DB::transaction(function () {
            $hrmsService = new HrmsService();
            if (!$hrmsService->syncEmployees()) {
                throw new \Exception("Employee sync failed.");
            }
        });

        return response()->json([
            'message' => 'Successfully synced all employees.',
            'success' => true,
        ]);
    }

    public function syncDepartments(Request $request)
    {
        DB::transaction(function () {
            $hrmsService = new HrmsService();
            if (!$hrmsService->syncDepartments()) {
                throw new \Exception("Department sync failed.");
            }
        });

        return response()->json([
            'message' => 'Successfully synced all departments.',
            'success' => true,
        ]);
    }

    public function syncUsers(Request $request)
    {
        DB::transaction(function () {
            $hrmsService = new HrmsService();
            if (!$hrmsService->syncUsers()) {
                throw new \Exception("User sync failed.");
            }
        });

        return response()->json([
            'message' => 'Successfully synced all users.',
            'success' => true,
        ]);
    }
}
