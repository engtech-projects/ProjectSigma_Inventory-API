<?php

use App\Http\Controllers\Actions\Approvals\ApproveApproval;
use App\Http\Controllers\Actions\Approvals\CancelApproval;
use App\Http\Controllers\Actions\Approvals\DisapproveApproval;
use App\Http\Controllers\Actions\Approvals\VoidApproval;
use App\Http\Controllers\ApiServiceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApiSyncController;
use App\Http\Controllers\ItemGroupController;
use App\Http\Controllers\ItemProfileBulkUploadController;
use App\Http\Controllers\UOMController;
use App\Http\Controllers\ItemProfileController;
use App\Http\Controllers\RequestBOMController;
use App\Http\Controllers\RequestItemProfilingController;
use App\Http\Controllers\RequestSupplierController;
use App\Http\Controllers\RequestSupplierUploadController;
use App\Http\Controllers\UOMGroupController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WarehousePssController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\PriceQuotationController;
use App\Http\Controllers\PriceQuotationItemController;
use App\Http\Controllers\RequestCanvassSummaryController;
use App\Http\Controllers\RequestNcpoController;
use App\Http\Controllers\SetupListsController;
use App\Http\Controllers\RequestProcurementCanvasserController;
use App\Http\Controllers\RequestProcurementController;
use App\Http\Controllers\RequestPurchaseOrderController;
use App\Http\Controllers\RequestRequisitionSlipController;
use App\Http\Controllers\TransactionMaterialReceivingController;
use App\Http\Controllers\TransactionMaterialReceivingItemController;
use App\Http\Controllers\RequestWithdrawalController;
use App\Http\Controllers\Actions\Approvals\RequestWithdrawalMyApprovals;
use App\Http\Controllers\ConsolidatedRequestController;
use App\Http\Controllers\RequestTurnoverController;
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
    Route::prefix('accounting')->group(function () {
    Route::prefix('purchase-order')->group(function () {
        Route::get('display-details/{id}', [RequestPurchaseOrderController::class, 'displayDetails']);
    });
});
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
        Route::get('list', [UOMController::class, 'list']);
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
        Route::get('item-list', [ItemProfileController::class, 'itemlist']);
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
        Route::get('overview/{warehouse}', [WarehouseController::class, 'show']);
        Route::patch('set-pss/{warehouse}', [WarehousePssController::class, 'update']);
        Route::get('logs/{warehouse_id}', [WarehouseController::class, 'getLogs']);
        Route::get('stocks/{warehouse}', [WarehouseController::class, 'getStocks']);
        Route::get('material-receivings/{warehouse}', [TransactionMaterialReceivingController::class, 'materialReceivingByWarehouse']);
        Route::prefix('request-withdrawal')->group(function () {
            Route::apiResource('resource', RequestWithdrawalController::class);
            Route::get('my-approvals', RequestWithdrawalMyApprovals::class);
        });
        // request turnovers
        Route::prefix('request-turnovers')->group(function () {
            Route::resource('resource', RequestTurnoverController::class)->names("requestTurnoverresource");
            Route::post('/update', [RequestTurnoverController::class, 'update']);
            Route::get('/incoming/{warehouse}', [RequestTurnoverController::class, 'incoming']);
            Route::get('/outgoing/{warehouse}', [RequestTurnoverController::class, 'outgoing']);
            Route::get('items/{warehouse}', [RequestTurnoverController::class, 'getItemsByWarehouse']);
            Route::get('all-request', [RequestTurnoverController::class, 'allRequests']);
            Route::get('my-approvals', [RequestTurnoverController::class, 'myApprovals']);
        });
    });

    Route::prefix('request-requisition-slip')->group(function () {
        Route::resource('resource', RequestRequisitionSlipController::class)->names("requisitionSlipRouteResource");
        Route::post('{requisitionSlip}/items/{item}/allocate-stock', [
            RequestRequisitionSlipController::class, 'allocateStock'
        ])->name('requisitionSlip.allocateStock');
        Route::get('all-request', [RequestRequisitionSlipController::class, 'allRequests']);
        Route::get('my-request', [RequestRequisitionSlipController::class, 'myRequests']);
        Route::get('my-approvals', [RequestRequisitionSlipController::class, 'myApprovals']);
    });

    Route::prefix('bom')->group(function () {
        Route::resource('resource', RequestBOMController::class)->names("requestBomresource");
        Route::get('current', [RequestBomController::class, 'getCurrentBom']);
        Route::get('list', [RequestBomController::class, 'getList']);
        Route::get('all-request', [RequestBOMController::class, 'allRequests']);
        Route::get('my-request', [RequestBOMController::class, 'myRequests']);
        Route::get('my-approvals', [RequestBOMController::class, 'myApprovals']);
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
                Route::post('/accessibilities', [ApiSyncController::class, 'syncAccessibilities']);
            });
        });
        Route::prefix('lists')->group(function () {
            Route::get('/department', [SetupListsController::class, 'getDepartmentList']);
            Route::get('/employee', [SetupListsController::class, 'getEmployeeList']);
            Route::get('/users', [SetupListsController::class, 'getUsersList']);
            Route::get('/project', [SetupListsController::class, 'getProjectlist']);
            Route::get('/warehouse', [SetupListsController::class, 'getWarehouseList']);
        });
    });
    Route::prefix('request-supplier')->group(function () {
        Route::resource('resource', RequestSupplierController::class)->names("requestSupplierresource");
        Route::resource('uploads', RequestSupplierUploadController::class)->names("supplierUploadresource");
        Route::get('all-request', [RequestSupplierController::class, 'allRequests']);
        Route::get('my-request', [RequestSupplierController::class, 'myRequests']);
        Route::get('my-approvals', [RequestSupplierController::class, 'myApprovals']);
        Route::get('approved-request', [RequestSupplierController::class, 'allApprovedRequests']);
        Route::get('search', [RequestSupplierController::class, 'search']);
    });
    Route::prefix('enum')->group(function () {
        Route::get('suppliers', [RequestSupplierController::class, 'list']);
    });

    Route::prefix('material-receiving')->group(function () {
        Route::resource('resource', TransactionMaterialReceivingController::class)->names("materialReceivingresource")
        ->only(['index', 'update', 'show']);
        Route::prefix('item')->group(function () {
            Route::resource('resource', TransactionMaterialReceivingItemController::class)->names("materialReceivingItemresource");
            Route::patch('{resource}/accept-all', [TransactionMaterialReceivingItemController::class, 'acceptAll']);
            Route::patch('{resource}/accept-some', [TransactionMaterialReceivingItemController::class, 'acceptSome']);
            Route::patch('{resource}/reject', [TransactionMaterialReceivingItemController::class, 'reject']);
        });
    });

    Route::prefix('export')->group(function () {
        Route::get('item-list', [ExportController::class, 'itemListGenerate'])->middleware('throttle:exports');
    });
    Route::prefix('procurement-request')->group(function () {
        Route::resource('resource', RequestProcurementController::class)->names("requestProcurement");
        Route::post('set-canvasser/{requestProcurement}', [RequestProcurementCanvasserController::class, 'setCanvasser']);
        Route::get('unserved', [RequestProcurementController::class, 'unservedRequests']);
        Route::post('{requestProcurement}/create-price-quotation', [PriceQuotationController::class, 'store']);
        Route::get('price-quotation/{priceQuotation}', [PriceQuotationController::class, 'show']);
        Route::get('quotations-for-canvass/{requestProcurement}', [PriceQuotationController::class, 'quotations'])
            ->name('procurement-request.quotations-for-canvass');
        Route::resource('price-quotation-item', PriceQuotationItemController::class)
            ->only(['update']);
        Route::prefix('canvass-summary')->group(function () {
            Route::resource('resource', RequestCanvassSummaryController::class)->names("requestCanvassSummary");
            Route::get('all-request', [RequestCanvassSummaryController::class, 'allRequests']);
            Route::get('my-approvals', [RequestCanvassSummaryController::class, 'myApprovals']);
            Route::get('{requestCanvassSummary}', [RequestCanvassSummaryController::class, 'show']);
        });
        Route::prefix('purchase-order')->group(function () {
            Route::resource('resource', RequestPurchaseOrderController::class)->names("requestPurchaseOrder");
            Route::patch('{requestPurchaseOrder}/update-processing-status', [RequestPurchaseOrderController::class, 'updateProcessingStatus']);
            Route::get('{resource}/detailed', [RequestPurchaseOrderController::class, 'showDetailed'])
                ->name('purchase-orders.detailed');
            Route::get('all-request', [RequestPurchaseOrderController::class, 'allRequests']);
        });
        Route::prefix('ncpo')->group(function () {
            Route::resource('resource', RequestNcpoController::class)->names("requestNcpo");
            Route::get('all-request', [RequestNcpoController::class, 'allRequests']);
            Route::get('my-approvals', [RequestNcpoController::class, 'myApprovals']);
        });
    });
    Route::prefix('consolidated-request')->group(function () {
        Route::resource('resource', ConsolidatedRequestController::class)->names("consolidatedRequest");
        Route::get('unserved', [ConsolidatedRequestController::class, 'unserved']);
        Route::post('generate-consolidated-request', [ConsolidatedRequestController::class, 'generateDraft']);
        Route::post('create-consolidated-request', [ConsolidatedRequestController::class, 'store']);
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
