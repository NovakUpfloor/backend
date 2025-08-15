<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\PropertyApiController;
use App\Http\Controllers\Api\BeritaApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\PackageApiController;
use App\Http\Controllers\Api\UserDashboardApiController;
use App\Http\Controllers\Api\AdminApiController;

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

Route::prefix('v1')->group(function () {
    
    // ===================================================================
    // RUTE PUBLIK (Tidak Perlu Login)
    // ===================================================================
    
    // --- Autentikasi ---
    Route::post('/auth/register', [AuthApiController::class, 'register']);
    Route::post('/auth/login', [AuthApiController::class, 'login']);
    Route::get('/auth/verify/{token}', [AuthApiController::class, 'verifyEmail']);

    // --- Konten Publik ---
    Route::get('/properties', [PropertyApiController::class, 'index']);
    Route::get('/properties/{id}', [PropertyApiController::class, 'show']);
    Route::get('/properties/{id}/share', [PropertyApiController::class, 'getShareableContent']);
    
    Route::get('/articles', [BeritaApiController::class, 'index']);
    Route::get('/articles/{id}', [BeritaApiController::class, 'show']);
    
    Route::get('/packages', [PackageApiController::class, 'index']);


    // ===================================================================
    // RUTE TERPROTEKSI (Wajib Login & Mengirim Token)
    // ===================================================================
    Route::middleware('auth:sanctum')->group(function () {
        
        // --- Logout ---
        Route::post('/auth/logout', [AuthApiController::class, 'logout']);

        // --- Rute Dashboard (User & Admin) ---
        Route::prefix('dashboard')->group(function () {
            Route::post('/purchase-package', [DashboardApiController::class, 'purchasePackage']);
            Route::get('/my-properties', [DashboardApiController::class, 'getMyProperties']);
            Route::post('/property', [DashboardApiController::class, 'storeProperty']);
            // Route::get('/stats', [UserDashboardApiController::class, 'stats']);
            // Route::get('/profile', [UserDashboardApiController::class, 'getProfile']);
            // Route::post('/profile', [UserDashboardApiController::class, 'updateProfile']);
            // Route::post('/purchase', [UserDashboardApiController::class, 'purchasePackage']);
        });

        // --- Rute Khusus Admin ---
        Route::middleware('admin')->prefix('admin')->group(function () {
            Route::get('/activations', [AdminApiController::class, 'getActivations']);
            Route::post('/activations/{id}/approve', [AdminApiController::class, 'approveActivation']);
            Route::get('/members', [AdminApiController::class, 'getMembers']);
            Route::post('/members/{id_staff}/status', [AdminApiController::class, 'updateStatus']);
            Route::delete('/members/{id_staff}', [AdminApiController::class, 'deleteMember']);
        });
		
		// --- ROUTE BARU UNTUK FASE 4 ---
		Route::get('/dashboard/stats', [UserDashboardApiController::class, 'stats']);
		// --- ROUTE BARU UNTUK PENCARIAN ---
		Route::get('/properties/search', [PropertyApiController::class, 'search']);

		// Route yang sudah ada
		Route::get('/properties', [PropertyApiController::class, 'index']);
		Route::get('/properties/{id}', [PropertyApiController::class, 'show']);
	
    });

});