<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;

Route::get('/',[AuthController::class, 'login'])->name('login');
Route::get('login', [AuthController::class, 'login'])->name('login');
Route::post('login',[AuthController::class, 'authenticate'])->name('login.authenticate');

Route::get('register',[AuthController::class, 'register'])->name('register');
Route::post('register',[AuthController::class, 'store'])->name('register.store');

Route::post('logout',[AuthController::class, 'logout'])->name('logout');

Route::group(['middleware' => 'auth'], function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('users',UserController::class);
});