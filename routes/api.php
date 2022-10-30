<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CodesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemoController;
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

// Auth routes
Route::post('/login', [LoginController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/check-user-logged', [LoginController::class, 'checkUserLogged']);

    // Dashboard
    Route::get('/stats', [DashboardController::class, 'getStats']);

    // Categories
    Route::apiResource('categories', CategoryController::class);

    // Demo
    Route::apiResource('demo', DemoController::class);

    // Links
    Route::apiResource('links', LinksController::class);
    Route::delete('links-multiple', [LinksController::class, 'destroyMultiple']);
    Route::post('/links/incremet-views/{id}', [LinksController::class, 'incrementViews']);

    // Codes
    Route::apiResource('codes', CodesController::class);
    Route::delete('codes-multiple', [CodesController::class, 'destroyMultiple']);
});
