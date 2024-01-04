<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CodeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemoController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ZaidaWebsiteController;
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

// Zaida website
Route::post('/contact-form', [ZaidaWebsiteController::class, 'sendContactEmail']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/check-user-logged', [LoginController::class, 'checkUserLogged']);

    // Dashboard
    Route::get('/stats', [DashboardController::class, 'getStats']);

    // Categories
    Route::apiResource('categories', CategoryController::class);

    // Users
    Route::apiResource('users', UserController::class);
    Route::delete('/users-multiple', [UserController::class, 'destroyMultiple']);

    // Roles
    Route::apiResource('roles', RoleController::class);
    Route::delete('/roles-multiple', [CodeController::class, 'destroyMultiple']);

    // Permissions
    Route::get('/permissions', [PermissionController::class, 'index']);

    // Links
    Route::apiResource('links', LinkController::class);
    Route::delete('/links-multiple', [LinkController::class, 'destroyMultiple']);
    Route::post('/links/incremet-views/{id}', [LinkController::class, 'incrementViews']);

    // Codes
    Route::apiResource('codes', CodeController::class);
    Route::delete('/codes-multiple', [CodeController::class, 'destroyMultiple']);

    // Demos
    Route::apiResource('demos', DemoController::class);
});
