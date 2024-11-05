<?php

use App\Http\Controllers\Actions\Approvals\ApproveApproval;
use App\Http\Controllers\Actions\Approvals\DisapproveApproval;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DepartmentsController;
use App\Http\Controllers\DetailsController;
use App\Http\Controllers\ItemGroupController;
use App\Http\Controllers\ItemProfileBulkUploadController;
use App\Http\Controllers\UOMController;
use App\Http\Controllers\ItemProfileController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\RequestBOMController;
use App\Http\Controllers\RequestItemProfilingController;
use App\Http\Controllers\UOMGroupController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WarehousePssController;
use App\Http\Controllers\WarehouseTransactionController;
use App\Http\Controllers\WarehouseTransactionItemController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::middleware('auth:api')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('/user', [AuthController::class, 'show']);
    });

    Route::prefix('item-group')->group(function () {
        Route::resource('resource', ItemGroupController::class)->names("itemGroupresource");
        Route::get('list', [ItemGroupController::class, 'get']);
        Route::get('search', [ItemGroupController::class, 'search']);
    });
    Route::prefix('uom')->group(function () {
        Route::resource('resource', UOMController::class)->names("uomresource");
        Route::get('group', [UOMGroupController::class, 'get']);
        Route::get('all', [UOMController::class, 'get']);
    });
    Route::prefix('uom-group')->group(function () {
        Route::resource('resource', UOMGroupController::class)->names("uomGroupresource");
    });
    Route::prefix('item-profile')->group(function () {
        Route::prefix('new-request')->group(function () {
            Route::resource('resource', RequestItemProfilingController::class)->names("itemProfilegresource");
            Route::get('all-request', [RequestItemProfilingController::class, 'allRequests']);
            Route::get('my-request', [RequestItemProfilingController::class, 'myRequests']);
            Route::get('my-approvals', [RequestItemProfilingController::class, 'myApprovals']);
        });
        Route::get('list', [RequestItemProfilingController::class, 'get']);
        Route::get('search', [ItemProfileController::class, 'search']);
        Route::patch('{resource}/activate', [ItemProfileController::class, 'activate']);
        Route::patch('{resource}/deactivate', [ItemProfileController::class, 'deactivate']);
        Route::post('bulk-upload', [ItemProfileBulkUploadController::class, 'bulkUpload']);
        Route::post('bulk-save', [ItemProfileBulkUploadController::class, 'bulkSave']);
    });
    Route::prefix('approvals')->group(function () {
        Route::post('approve/{modelName}/{model}', ApproveApproval::class);
        Route::post('disapprove/{modelName}/{model}', DisapproveApproval::class);
    });
    Route::prefix('warehouse')->group(function () {
        Route::resource('resource', WarehouseController::class)->names("warehouseresource");
        Route::get('overview/{warehouse_id}', [WarehouseController::class, 'show']);
        Route::patch('set-pss/{warehouse_id}', [WarehousePssController::class, 'update']);
        Route::get('logs/{warehouse_id}', [WarehouseController::class, 'getLogs']);

        Route::prefix('transaction')->group(function () {
            Route::resource('resource', WarehouseTransactionController::class)->names("warehouseTransactionsresource");
        });
        Route::prefix('transaction-item')->group(function () {
            Route::resource('resource', WarehouseTransactionItemController::class)->names("warehouseTransactionItemresource");
        });
    });
    Route::prefix('bom')->group(function () {
        Route::resource('resource', RequestBOMController::class)->names("requestBomresource");
        Route::get('current', [RequestBomController::class, 'getCurrentBom']);
        Route::get('list', [RequestBomController::class, 'getList']);
        Route::get('all-request', [RequestBomController::class, 'allRequests']);
        Route::get('my-request', [RequestBomController::class, 'myRequests']);
        Route::get('my-approvals', [RequestBomController::class, 'myApprovals']);

        Route::prefix('details')->group(function () {
            Route::resource('resource', DetailsController::class)->names("bomDetailsresource");
        });
    });
    Route::prefix('setup')->group(function () {
        // to be used later
        // Route::prefix('item-group')->group(function () {
        //     Route::resource('resource', ItemGroupController::class)->names("itemGroupresource");
        //     Route::get('list', [ItemGroupController::class, 'get']);
        //     Route::get('search', [ItemGroupController::class, 'search']);
        // });
        // Route::prefix('uom')->group(function () {
        //     Route::resource('resource', UOMController::class)->names("uomresource");
        //     Route::get('group', [UOMGroupController::class, 'get']);
        //     Route::get('all', [UOMController::class, 'get']);
        // });
        // Route::prefix('uom-group')->group(function () {
        //     Route::resource('resource', UOMGroupController::class)->names("uomGroupresource");
        // });

        Route::resource('sync-departments', DepartmentsController::class)->names("syncDepartmentsresource");
        Route::resource('sync-projects', ProjectsController::class)->names("syncProjectsresource");
    });
});
