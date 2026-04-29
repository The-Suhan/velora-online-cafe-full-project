<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ForgotPasswordController;

// ── Auth ──────────────────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);

// ── Forgot Password ───────────────────────────────────────────
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendOtp']);
Route::post('/forgot-password/verify', [ForgotPasswordController::class, 'verifyOtp']);
Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'resetPassword']);

// ── Protected ─────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // ── Admin only ────────────────────────────────────────────
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/orders/chart', [AdminController::class, 'ordersChart']);
        Route::get('/users', [AdminController::class, 'users']);       
        Route::get('/users/stats', [AdminController::class, 'userStats']);
        Route::get('/users/{user}', [AdminController::class, 'showUser']);  
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser']);
    });
});
