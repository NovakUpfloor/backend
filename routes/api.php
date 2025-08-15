<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\PropertyApiController;
use App\Http\Controllers\Api\BeritaApiController;
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

    // --- Konten Publik ---
    Route::get('/properties/search', [PropertyApiController::class, 'search']);
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

        // --- Rute User Panel (Agen/Member) ---
        Route::prefix('user-panel')->group(function () {
            Route::get('/stats', [UserDashboardApiController::class, 'stats']);
            Route::get('/profile', [UserDashboardApiController::class, 'getProfile']);
            Route::post('/profile', [UserDashboardApiController::class, 'updateProfile']);
            Route::post('/purchase-new-package', [UserDashboardApiController::class, 'purchasePackage']);
            Route::get('/purchases', [UserDashboardApiController::class, 'getPurchaseHistory']);
            Route::post('/purchases/{id}/upload-proof', [UserDashboardApiController::class, 'updatePaymentProof']);
            Route::post('/property', [UserDashboardApiController::class, 'storeProperty']);
        });

        // --- Rute Admin Panel ---
        Route::middleware('admin')->prefix('admin-panel')->group(function () {
            // Konfirmasi Pembelian
            Route::get('/purchase-confirmations', [AdminApiController::class, 'getPurchaseConfirmations']);
            Route::post('/purchase-confirmations/{id}/update-status', [AdminApiController::class, 'updatePurchaseStatus']);

            // Manajemen Agen
            Route::get('/agents', [AdminApiController::class, 'getAgents']);
            Route::post('/agents/{id_staff}/update-status', [AdminApiController::class, 'updateAgentStatus']);
            Route::delete('/agents/{id_staff}', [AdminApiController::class, 'deleteAgent']);

            // Manajemen Properti
            Route::get('/agents/{id_staff}/properties', [AdminApiController::class, 'getAgentProperties']);
            Route::post('/properties/{id_property}/update-status', [AdminApiController::class, 'updatePropertyStatus']);

            // Statistik
            Route::get('/agents/{id_staff}/stats', [AdminApiController::class, 'getAgentStats']);
        });
	
    });

});