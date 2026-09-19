<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PasswordResetController;

Route::get('/',[AuthController::class, 'login'])->name('root');
Route::get('login', [AuthController::class, 'login'])->name('login');
Route::post('login',[AuthController::class, 'authenticate'])->name('login.authenticate');

Route::get('register',[AuthController::class, 'register'])->name('register');
Route::post('register',[AuthController::class, 'store'])->name('register.store');

Route::post('logout',[AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('users',UserController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class)->only(['store', 'destroy']);

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class,'updatePassword'])->name('profile.password.update');
});

Route::middleware('guest')->group(function () {
    Route::get('forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('reset-password', [PasswordResetController::class, 'update'])->name('password.update');
});