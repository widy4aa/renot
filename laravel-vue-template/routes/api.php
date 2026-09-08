<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CertificationCategoryController;
use App\Http\Controllers\Pegawai\DashboardController as PegawaiDashboard;
use App\Http\Controllers\Pegawai\DocumentController as PegawaiDocument;
use App\Http\Controllers\Pegawai\NotificationController as PegawaiNotification;
use App\Http\Controllers\Pegawai\ProfileController as PegawaiProfile;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Shared: kategori sertifikasi
    Route::get('/certification-categories', [CertificationCategoryController::class, 'index']);

    // Pegawai routes
    Route::middleware('role:pegawai')->prefix('pegawai')->group(function () {
        Route::get('/dashboard', PegawaiDashboard::class);

        Route::get('/profile', [PegawaiProfile::class, 'show']);
        Route::post('/profile', [PegawaiProfile::class, 'update']);
        Route::put('/password', [PegawaiProfile::class, 'changePassword']);

        Route::get('/documents', [PegawaiDocument::class, 'index']);
        Route::post('/documents', [PegawaiDocument::class, 'store']);
        Route::get('/documents/export', [PegawaiDocument::class, 'export']);
        Route::get('/documents/{document}', [PegawaiDocument::class, 'show']);
        Route::post('/documents/{document}', [PegawaiDocument::class, 'update']);
        Route::delete('/documents/{document}', [PegawaiDocument::class, 'destroy']);
        Route::get('/documents/{document}/download', [PegawaiDocument::class, 'download']);

        Route::get('/notifications', [PegawaiNotification::class, 'index']);
        Route::post('/notifications/read-all', [PegawaiNotification::class, 'markAllAsRead']);
        Route::post('/notifications/{notification}/read', [PegawaiNotification::class, 'markAsRead']);
    });
});
