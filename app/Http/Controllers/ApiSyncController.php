<?php

namespace App\Http\Controllers;

use App\Http\Services\ApiServices\AccountingSecretKeyService;
use App\Http\Services\ApiServices\HrmsSecretKeyService;
use App\Http\Services\ApiServices\ProjectMonitoringSecretKeyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiSyncController extends Controller
{
    public function syncAll(Request $request)
    {
        DB::transaction(function () {
            $accountingService = new AccountingSecretKeyService();
            $hrmsService = new HrmsSecretKeyService();
            $projectService = new ProjectMonitoringSecretKeyService();
            $errorServices = [];
            if (!$accountingService->syncAll()) {
                $errorServices[] = "Accounting";
            }
            if (!$hrmsService->syncAll()) {
                $errorServices[] = "HRMS";
            }
            if (!$projectService->syncAll()) {
                $errorServices[] = "Project Monitoring";
            }
            if (!empty($errorServices)) {
                throw new \Exception('Sync with ' . implode(', ', $errorServices) . ' failed while trying to sync with all API Services');
            }
        });
        return response()->json([
            'message' => 'Successfully synced with all API services.',
            'success' => true,
        ]);
    }
    // Accounting
    public function syncAllAccounting(Request $request)
    {
        return response()->json([
            'message' => 'No Services to sync with yet.',
            'success' => true,
        ], 202);
        // PREPARED CODE
        // DB::transaction(function () {
        //     $accountingService = new AccountingSecretKeyService();
        //     if (!$accountingService->syncAll()) {
        //         throw new \Exception("Accounting sync failed.");
        //     }
        // });
        // return response()->json([
        //     'message' => 'Successfully synced with Accounting API service.',
        //     'success' => true,
        // ]);
    }
    // HRMS
    public function syncAllHrms(Request $request)
    {
        DB::transaction(function () {
            $hrmsService = new HrmsSecretKeyService();
            if (!$hrmsService->syncAll()) {
                throw new \Exception("HRMS sync failed.");
            }
        });

        return response()->json([
            'message' => 'Successfully synced with HRMS API service.',
            'success' => true,
        ]);
    }
    public function syncEmployees(Request $request)
    {
        DB::transaction(function () {
            $hrmsService = new HrmsSecretKeyService();
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
            $hrmsService = new HrmsSecretKeyService();
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
            $hrmsService = new HrmsSecretKeyService();
            if (!$hrmsService->syncUsers()) {
                throw new \Exception("User sync failed.");
            }
        });

        return response()->json([
            'message' => 'Successfully synced all users.',
            'success' => true,
        ]);
    }
    // Project Monitoring
    public function syncAllProjectMonitoring(Request $request)
    {
        DB::transaction(function () {
            $projectService = new ProjectMonitoringSecretKeyService();
            if (!$projectService->syncAll()) {
                throw new \Exception("Project monitoring sync failed.");
            }
        });

        return response()->json([
            'message' => 'Successfully synced with Project Monitoring API service.',
            'success' => true,
        ]);
    }
    public function syncProjects(Request $request)
    {
        DB::transaction(function () {
            $projectService = new ProjectMonitoringSecretKeyService();
            if (!$projectService->syncProjects()) {
                throw new \Exception("Project sync failed.");
            }
        });

        return response()->json([
            'message' => 'Successfully synced all projects.',
            'success' => true,
        ]);
    }
}
