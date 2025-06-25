<?php

namespace App\Http\Controllers;

use App\Http\Services\ApiServices\AccountingSecretKeyService;
use App\Jobs\ApiHrmsSyncJob;
use App\Jobs\ApiProjectsSyncJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApiSyncController extends Controller
{
    public function syncAll(Request $request)
    {
        DB::transaction(function () {
            $errorServices = [];
            try {
                ApiHrmsSyncJob::dispatch('syncAll');
            } catch (\Exception $e) {
                $errorServices[] = "HRMS";
                Log::error('Failed to dispatch HRMS sync job', ['error' => $e->getMessage()]);
            }
            if (!ApiProjectsSyncJob::dispatch('syncAll')) {
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
        try {
            ApiHrmsSyncJob::dispatch('syncAll');
        } catch (\Exception $e) {
            Log::error('Failed to dispatch HRMS sync job', ['error' => $e->getMessage()]);
            throw new \Exception("HRMS sync failed: " . $e->getMessage());
        }
        return response()->json([
            'message' => 'Successfully synced with HRMS API service.',
            'success' => true,
        ]);
    }
    public function syncEmployees(Request $request)
    {
        try {
            ApiHrmsSyncJob::dispatch('syncEmployees');
        } catch (\Exception $e) {
            Log::error('Failed to dispatch Employee sync job', ['error' => $e->getMessage()]);
            throw new \Exception("Employee sync failed: " . $e->getMessage());
        }
        return response()->json([
            'message' => 'Successfully synced all employees.',
            'success' => true,
        ]);
    }
    public function syncDepartments(Request $request)
    {
        if (!ApiHrmsSyncJob::dispatch('syncDepartments')) {
            throw new \Exception("Department sync failed.");
        }
        return response()->json([
            'message' => 'Successfully synced all departments.',
            'success' => true,
        ]);
    }
    public function syncUsers(Request $request)
    {
        if (!ApiHrmsSyncJob::dispatch('syncUsers')) {
            throw new \Exception("User sync failed.");
        }
        return response()->json([
            'message' => 'Successfully synced all users.',
            'success' => true,
        ]);
    }
    // Project Monitoring
    public function syncAllProjectMonitoring(Request $request)
    {
        if (!ApiProjectsSyncJob::dispatch('syncAll')) {
            throw new \Exception("Project monitoring sync failed.");
        }
        return response()->json([
            'message' => 'Successfully synced with Project Monitoring API service.',
            'success' => true,
        ]);
    }
    public function syncProjects(Request $request)
    {
        if (!ApiProjectsSyncJob::dispatch('syncProjects')) {
            throw new \Exception("Project sync failed.");
        }
        return response()->json([
            'message' => 'Successfully synced all projects.',
            'success' => true,
        ]);
    }
}
