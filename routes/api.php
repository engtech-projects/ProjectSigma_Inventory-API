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
        Route::resource('resource', ItemGroupController::class)->names("itemGroupresource");
        Route::get('list', [ItemGroupController::class, 'get']);
        Route::get('search', [ItemGroupController::class, 'search']);
    });
    Route::prefix('uom')->group(function () {
        Route::resource('resource', UOMController::class)->names("uomresource");
    });
    Route::prefix('item-profile')->group(function () {
        Route::prefix('new-request')->group(function () {
            Route::resource('resource', RequestItemProfilingController::class)->names("itemProfilegresource");
            Route::get('all-request', [RequestItemProfilingController::class, 'allRequests']);
            Route::get('my-request', [RequestItemProfilingController::class, 'myRequests']);
            Route::get('my-approvals', [RequestItemProfilingController::class, 'myApprovals']);
        });
        Route::get('list', [RequestItemProfilingController::class, 'get']);
        Route::get('{requestId}', [RequestItemProfilingController::class, 'show']);
        Route::patch('{resource}/activate', [ItemProfileController::class, 'activate']);
        Route::patch('{resource}/deactivate', [ItemProfileController::class, 'deactivate']);
    });
    // Route::resource('approvals', ApprovalsController::class);
    Route::prefix('approvals')->group(function () {
        Route::post('approve/{modelName}/{model}', ApproveApproval::class);
        Route::post('disapprove/{modelName}/{model}', DisapproveApproval::class);
    });
});
