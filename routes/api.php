<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CodesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LinksController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/check-user-role', [UserController::class, 'checkUserRole']);

    // Dashboard
    Route::get('/stats', [DashboardController::class, 'getStats']);

    // Categories
    Route::apiResource('categories', CategoryController::class);

    // Links
    Route::apiResource('links', LinksController::class);
    Route::delete('links-multiple', [LinksController::class, 'destroyMultiple']);
    Route::post('/links/incremet-views/{id}', [LinksController::class, 'incrementViews']);

    // Codes
    Route::apiResource('codes', CodesController::class);
});

Route::post('/login', [LoginController::class, 'login']);
