<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ItemGroupController;
use App\Http\Controllers\UOMController;

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


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('item-group')->group(function () {
    Route::resource('resource', ItemGroupController::class);
    Route::get('list', [ItemGroupController::class, 'get']);
    Route::get('search', [ItemGroupController::class, 'search']);
});
Route::prefix('uom')->group(function () {
    Route::resource('resource', UOMController::class);
});
