<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ItemGroupController;
use App\Http\Controllers\UOMController;
use App\Http\Controllers\ItemProfileController;

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
    // return $request->user();

    // Route::prefix('item-group')->group(function () {
    //     Route::resource('resource', ItemGroupController::class);
    //     Route::get('list', [ItemGroupController::class, 'get']);
    //     Route::get('search', [ItemGroupController::class, 'search']);
    // });
    // Route::prefix('uom')->group(function () {
    //     Route::resource('resource', UOMController::class);
    // });
    // Route::prefix('item-profile')->group(function () {
    //     Route::resource('resource', ItemProfileController::class);
    //     Route::get('list', [ItemProfileController::class, 'get']);
    // });
});

Route::prefix('item-group')->group(function () {
    Route::resource('resource', ItemGroupController::class);
    Route::get('list', [ItemGroupController::class, 'get']);
    Route::get('search', [ItemGroupController::class, 'search']);
});
Route::prefix('uom')->group(function () {
    Route::resource('resource', UOMController::class);
});
Route::prefix('item-profile')->group(function () {
    Route::resource('resource', ItemProfileController::class);
    Route::get('list', [ItemProfileController::class, 'get']);
});
