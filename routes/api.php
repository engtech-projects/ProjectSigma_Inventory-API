<?php

use App\Http\Controllers\Actions\Approvals\ApproveApproval;
use App\Http\Controllers\Actions\Approvals\DisapproveApproval;
use App\Http\Controllers\ApprovalsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemGroupController;
use App\Http\Controllers\UOMController;
use App\Http\Controllers\ItemProfileController;
use App\Http\Controllers\RequestItemProfilingController;

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
        Route::resource('resource', ItemGroupController::class);
        Route::get('list', [ItemGroupController::class, 'get']);
        Route::get('search', [ItemGroupController::class, 'search']);
    });
    Route::prefix('uom')->group(function () {
        Route::resource('resource', UOMController::class)->name("uomresource");
    });
    Route::prefix('item-profile')->group(function () {
        Route::resource('resource', ItemProfileController::class);
        Route::get('all-request', [ItemProfileController::class, 'allRequests']);
        Route::get('my-request', [ItemProfileController::class, 'myRequests']);
        Route::get('my-approvals', [ItemProfileController::class, 'myApprovals']);
        Route::get('list', [ItemProfileController::class, 'get']);
    });
    // Route::resource('approvals', ApprovalsController::class);
    Route::prefix('approvals')->group(function () {
        Route::post('approve/{modelName}/{model}', ApproveApproval::class);
        Route::post('disapprove/{modelName}/{model}', DisapproveApproval::class);
    });
});

// Route::prefix('item-group')->group(function () {
//     Route::resource('resource', ItemGroupController::class);
//     Route::get('list', [ItemGroupController::class, 'get']);
//     Route::get('search', [ItemGroupController::class, 'search']);
// });
// Route::prefix('uom')->group(function () {
//     Route::resource('resource', UOMController::class);
//     Route::get('list', [UOMController::class, 'get']);
// });
// Route::prefix('item-profile')->group(function () {
//     Route::resource('resource', ItemProfileController::class);
//     Route::get('my-request', [ItemProfileController::class, 'myRequests']);
//     Route::get('my-approvals', [ItemProfileController::class, 'myApprovals']);
// });
// Route::resource('requests', RequestItemProfilingController::class)->only(['index', 'store']);
