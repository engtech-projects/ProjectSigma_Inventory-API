<?php

use App\Http\Controllers\Actions\Approvals\ApproveApproval;
use App\Http\Controllers\Actions\Approvals\CancelApproval;
use App\Http\Controllers\Actions\Approvals\DisapproveApproval;
use App\Http\Controllers\Actions\Approvals\VoidApproval;
use App\Http\Controllers\ApiServiceController;
use App\Http\Controllers\MaterialsReceivingController;
use App\Http\Controllers\MRRController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApiSyncController;
use App\Http\Controllers\DepartmentsController;
use App\Http\Controllers\DetailsController;
use App\Http\Controllers\ItemGroupController;
use App\Http\Controllers\ItemProfileBulkUploadController;
use App\Http\Controllers\UOMController;
use App\Http\Controllers\ItemProfileController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\RequestBOMController;
use App\Http\Controllers\RequestItemProfilingController;
use App\Http\Controllers\RequestStockController;
use App\Http\Controllers\RequestSupplierController;
use App\Http\Controllers\RequestSupplierUploadController;
use App\Http\Controllers\UOMGroupController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WarehousePssController;
use App\Http\Controllers\WarehouseTransactionController;
use App\Http\Controllers\WarehouseTransactionItemController;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Artisan;

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

// SECRET API KEY ROUTES
Route::middleware("secret_api")->group(function () {
    // SIGMA SERVICES ROUTES
    Route::prefix('sigma')->group(function () {
        Route::prefix("sync-list")->group(function () {
            Route::get('suppliers', [ApiServiceController::class, 'getSuppliersList']);
            Route::get('item-profiles', [ApiServiceController::class, 'getItemprofilesList']);
            Route::get('uoms', [ApiServiceController::class, 'getUomsList']);
        });
    });
});


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
            Route::resource('resource', RequestItemProfilingController::class)->names("requestitemProfilingresource");
            Route::get('all-request', [RequestItemProfilingController::class, 'allRequests']);
            Route::get('my-request', [RequestItemProfilingController::class, 'myRequests']);
            Route::get('my-approvals', [RequestItemProfilingController::class, 'myApprovals']);
        });
        Route::get('list', [RequestItemProfilingController::class, 'get']);
        Route::resource('resource', ItemProfileController::class)->names("itemProfileresource");

        Route::get('search', [ItemProfileController::class, 'search']);
        Route::patch('{resource}/activate', [ItemProfileController::class, 'activate']);
        Route::patch('{resource}/deactivate', [ItemProfileController::class, 'deactivate']);
        Route::post('bulk-upload', [ItemProfileBulkUploadController::class, 'bulkUpload']);
        Route::post('bulk-save', [ItemProfileBulkUploadController::class, 'bulkSave']);
    });
    Route::prefix('approvals')->group(function () {
        Route::post('approve/{modelName}/{model}', ApproveApproval::class);
        Route::post('disapprove/{modelName}/{model}', DisapproveApproval::class);
        Route::post('cancel/{modelName}/{model}', CancelApproval::class);
        Route::post('void/{modelName}/{model}', VoidApproval::class);
    });
    Route::prefix('warehouse')->group(function () {
        Route::resource('resource', WarehouseController::class)->names("warehouseresource");
        Route::resource('pss', WarehousePssController::class)->names("warehousePSSresource");
        Route::get('overview/{warehouse_id}', [WarehouseController::class, 'show']);
        Route::patch('set-pss/{warehouse_id}', [WarehousePssController::class, 'update']);
        Route::get('logs/{warehouse_id}', [WarehouseController::class, 'getLogs']);
        Route::get('stocks/{warehouse_id}', [WarehouseController::class, 'getStocks']);

        Route::get('materials-receiving/{warehouse_id}', [WarehouseController::class, 'withMaterialsReceiving']);

        Route::prefix('transaction')->group(function () {
            Route::resource('resource', WarehouseTransactionController::class)->names("warehouseTransactionsresource");
        });
        Route::prefix('transaction-item')->group(function () {
            Route::resource('resource', WarehouseTransactionItemController::class)->names("warehouseTransactionItemresource");
        });

        // Route::resource('stocks/{warehouse_id}', [RequestStockController::class, 'store']);
    });

    Route::prefix('request-stock')->group(function () {
        Route::resource('resource', RequestStockController::class)->names("requestStockresource");
        Route::get('all-request', [RequestStockController::class, 'allRequests']);
        Route::get('my-request', [RequestStockController::class, 'myRequests']);
        Route::get('my-approvals', [RequestStockController::class, 'myApprovals']);
    });

    Route::prefix('bom')->group(function () {
        Route::resource('resource', RequestBOMController::class)->names("requestBomresource");
        Route::get('current', [RequestBomController::class, 'getCurrentBom']);
        Route::get('list', [RequestBomController::class, 'getList']);

        Route::prefix('details')->group(function () {
            Route::resource('resource', DetailsController::class)->names("bomDetailsresource");
        });

        Route::get('all-request', [RequestBOMController::class, 'allRequests']);
        Route::get('my-request', [RequestBOMController::class, 'myRequests']);
        Route::get('my-approvals', [RequestBOMController::class, 'myApprovals']);
    });
    Route::prefix('departments')->group(function () {
        Route::resource('resource', DepartmentsController::class)->names("departmentresource");
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
        //DATA SYNC MANUAL USER TRIGGER
        Route::prefix('sync')->group(function () {
            Route::post('/all', [ApiSyncController::class, 'syncAll']);
            Route::prefix('project')->group(function () {
                Route::post('/all', [ApiSyncController::class, 'syncAllProjectMonitoring']);
                Route::post('/projects', [ApiSyncController::class, 'syncProjects']);
            });
            Route::prefix('hrms')->group(function () {
                Route::post('/all', [ApiSyncController::class, 'syncAllHrms']);
                Route::post('/employees', [ApiSyncController::class, 'syncEmployees']);
                Route::post('/users', [ApiSyncController::class, 'syncUsers']);
                Route::post('/departments', [ApiSyncController::class, 'syncDepartments']);
            });
        });
    });
    Route::prefix('request-supplier')->group(function () {
        Route::resource('resource', RequestSupplierController::class)->names("requestSupplierresource");
        Route::resource('uploads', RequestSupplierUploadController::class)->names("supplierUploadresource");
        Route::get('list', [RequestSupplierController::class, 'list']);
        Route::get('all-request', [RequestSupplierController::class, 'allRequests']);
        Route::get('my-request', [RequestSupplierController::class, 'myRequests']);
        Route::get('my-approvals', [RequestSupplierController::class, 'myApprovals']);
        Route::get('approved-request', [RequestSupplierController::class, 'allApprovedRequests']);
    });
    Route::prefix('enum')->group(function () {
        Route::get('suppliers', [RequestSupplierController::class, 'list']);
    });

    Route::prefix('material-receiving')->group(function () {
        Route::resource('resource', WarehouseTransactionController::class)->names("materialReceivingresource");
        Route::patch('{id}/save-details', [WarehouseTransactionController::class, 'saveDetails']);
        Route::put('{id}/update-reference', [WarehouseTransactionController::class, 'updateReference']);
        Route::get('warehouse/{warehouse_id}', [WarehouseTransactionController::class, 'getMaterialsReceivingByWarehouse']);
        Route::get('all-request', [WarehouseTransactionController::class, 'allRequests']);
        Route::prefix('item')->group(function () {
            Route::resource('resource', WarehouseTransactionItemController::class)->names("materialsReceivingItemresource");

            Route::patch('{resource}/accept-all', [WarehouseTransactionItemController::class, 'acceptAll']);
            Route::patch('{resource}/accept-with-details', [WarehouseTransactionItemController::class, 'acceptWithDetails']);
            Route::patch('{resource}/reject', [WarehouseTransactionItemController::class, 'reject']);
        });
    });

    Route::prefix('project')->group(function () {
        Route::resource('resource', ProjectsController::class)->names("projectsResource");
    });



    if (config()->get('app.artisan') == 'true') {
        Route::prefix('artisan')->group(function () {
            Route::get('storage', function () {
                Artisan::call("storage:link");
                return "success";
            });
            Route::get('optimize', function () {
                Artisan::call("optimize");
                return "success";
            });
            Route::get('optimize-clear', function () {
                Artisan::call("optimize:clear");
                return "success";
            });
        });
    }
});
