<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\SsoController;

// SSO: hub (SSO_HUB=true) menangani login/register/verifikasi/reset password untuk semua app,
// app client hanya meneruskan route bernama sama ke hub (lihat config/sso.php)
if (config('sso.hub')) {
    // Landing page hub, juga dibuka lewat domain induk (elips.id)
    Route::view('/', 'landing')->name('root');
    Route::get('login', [AuthController::class, 'login'])->name('login');
    Route::post('login',[AuthController::class, 'authenticate'])->middleware('throttle:10,1')->name('login.authenticate');

    Route::get('register',[AuthController::class, 'register'])->name('register');
    Route::post('register',[AuthController::class, 'store'])->middleware('throttle:5,1')->name('register.store');

    Route::post('logout',[AuthController::class, 'logout'])->name('logout');

    Route::middleware('auth')->group(function () {
        Route::get('email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
        Route::get('email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
        Route::post('email/verification-notification', [EmailVerificationController::class, 'resend'])->middleware('throttle:6,1')->name('verification.send');
    });

    Route::middleware('guest')->group(function () {
        Route::get('forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
        Route::post('forgot-password', [PasswordResetController::class, 'store'])->middleware('throttle:3,1')->name('password.email');
        Route::get('reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
        Route::post('reset-password', [PasswordResetController::class, 'update'])->name('password.update');
    });
} else {
    Route::redirect('/', '/dashboard')->name('root');
    Route::get('login', [SsoController::class, 'login'])->name('login');
    Route::get('register', [SsoController::class, 'register'])->name('register');
    Route::get('forgot-password', [SsoController::class, 'forgotPassword'])->name('password.request');
    Route::get('email/verify', [SsoController::class, 'verifyEmail'])->middleware('auth')->name('verification.notice');
    Route::post('logout', [SsoController::class, 'logout'])->name('logout');
}

Route::middleware(['auth', 'app.access', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Pengaturan user/role/permission terpusat: hanya di hub (semua app) atau app standalone (app sendiri).
    // App client mengarahkan ke halaman Users di hub dengan app ini terpilih.
    if (config('sso.hub')) {
        Route::middleware('managed.app')->group(function () {
            Route::resource('users',UserController::class);
            Route::resource('roles', RoleController::class);
            Route::resource('permissions', PermissionController::class)->only(['store', 'destroy']);
        });
    } else {
        Route::get('access', [SsoController::class, 'manageAccess'])->name('access.manage');
    }

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class,'updatePassword'])->name('profile.password.update');

    Route::get('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');

    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
});