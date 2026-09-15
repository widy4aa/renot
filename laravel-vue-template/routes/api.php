<?php

use App\Http\Controllers\Admin\ApprovalController as AdminApproval;
use App\Http\Controllers\Admin\AuditController as AdminAudit;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\DepartemenController as AdminDepartemen;
use App\Http\Controllers\Admin\DocumentController as AdminDocument;
use App\Http\Controllers\Admin\KategoriController as AdminKategori;
use App\Http\Controllers\Admin\ManajemenAdminController as AdminManajemen;
use App\Http\Controllers\Admin\NotificationController as AdminNotification;
use App\Http\Controllers\Admin\PegawaiController as AdminPegawai;
use App\Http\Controllers\Admin\PengaturanController as AdminPengaturan;
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

    // Admin routes
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', AdminDashboard::class);

        Route::get('/approval', [AdminApproval::class, 'index']);

        Route::get('/notifications', [AdminNotification::class, 'index']);
        Route::post('/notifications/read-all', [AdminNotification::class, 'markAllAsRead']);
        Route::post('/notifications/{notification}/read', [AdminNotification::class, 'markAsRead']);

        Route::get('/documents', [AdminDocument::class, 'index']);
        Route::post('/documents', [AdminDocument::class, 'store']);
        Route::get('/documents/export', [AdminDocument::class, 'export']);
        Route::get('/documents/{document}', [AdminDocument::class, 'show']);
        Route::post('/documents/{document}', [AdminDocument::class, 'update']);
        Route::delete('/documents/{document}', [AdminDocument::class, 'destroy']);
        Route::get('/documents/{document}/download', [AdminDocument::class, 'download']);
        Route::post('/documents/{document}/approve', [AdminDocument::class, 'approve']);
        Route::post('/documents/{document}/reject', [AdminDocument::class, 'reject']);

        Route::get('/departemen', [AdminDepartemen::class, 'index']);
        Route::post('/departemen', [AdminDepartemen::class, 'store']);
        Route::put('/departemen/{departemen}', [AdminDepartemen::class, 'update']);
        Route::delete('/departemen/{departemen}', [AdminDepartemen::class, 'destroy']);

        Route::get('/pegawai', [AdminPegawai::class, 'index']);
        Route::post('/pegawai', [AdminPegawai::class, 'store']);
        Route::get('/pegawai/{pegawai}', [AdminPegawai::class, 'show']);
        Route::post('/pegawai/{pegawai}', [AdminPegawai::class, 'update']);
        Route::delete('/pegawai/{pegawai}', [AdminPegawai::class, 'destroy']);
        Route::post('/pegawai/{pegawai}/toggle-active', [AdminPegawai::class, 'toggleActive']);
        Route::post('/pegawai/{pegawai}/reset-password', [AdminPegawai::class, 'resetPassword']);

        Route::get('/kategori', [AdminKategori::class, 'index']);
        Route::post('/kategori/{kategori}/types', [AdminKategori::class, 'storeType']);
        Route::put('/kategori/{kategori}/types/{type}', [AdminKategori::class, 'updateType']);
        Route::delete('/kategori/{kategori}/types/{type}', [AdminKategori::class, 'destroyType']);

        Route::get('/audit', [AdminAudit::class, 'index']);
        Route::get('/audit/types', [AdminAudit::class, 'activityTypes']);

        Route::get('/admins', [AdminManajemen::class, 'index']);
        Route::post('/admins', [AdminManajemen::class, 'store']);
        Route::put('/admins/{adminUser}', [AdminManajemen::class, 'update']);
        Route::delete('/admins/{adminUser}', [AdminManajemen::class, 'destroy']);
        Route::post('/admins/{adminUser}/reset-password', [AdminManajemen::class, 'resetPassword']);

        Route::get('/pengaturan/reminders', [AdminPengaturan::class, 'reminders']);
        Route::post('/pengaturan/reminders', [AdminPengaturan::class, 'storeReminder']);
        Route::patch('/pengaturan/reminders/{schedule}/toggle', [AdminPengaturan::class, 'toggleReminder']);
        Route::delete('/pengaturan/reminders/{schedule}', [AdminPengaturan::class, 'destroyReminder']);
        Route::get('/pengaturan/email-templates', [AdminPengaturan::class, 'emailTemplates']);
        Route::put('/pengaturan/email-templates/{template}', [AdminPengaturan::class, 'updateEmailTemplate']);
        Route::get('/pengaturan/mail-configs', [AdminPengaturan::class, 'mailConfigs']);
        Route::post('/pengaturan/mail-configs', [AdminPengaturan::class, 'storeMailConfig']);
        Route::put('/pengaturan/mail-configs/{mailConfig}', [AdminPengaturan::class, 'updateMailConfig']);
        Route::delete('/pengaturan/mail-configs/{mailConfig}', [AdminPengaturan::class, 'destroyMailConfig']);
        Route::post('/pengaturan/mail-configs/{mailConfig}/activate', [AdminPengaturan::class, 'activateMailConfig']);
        Route::post('/pengaturan/mail-configs/{mailConfig}/test', [AdminPengaturan::class, 'testMailConfig']);
    });

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
