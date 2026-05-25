<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\OrderController;
// Client
use App\Http\Controllers\ProductController;

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
        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/orders/chart', [AdminController::class, 'ordersChart']);

        // Users
        Route::get('/users', [AdminController::class, 'users']);
        Route::get('/users/stats', [AdminController::class, 'userStats']);
        Route::get('/users/{user}', [AdminController::class, 'showUser']);
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser']);

        // Categories — stats/index order
        Route::get('/categories/parents', [CategoryController::class, 'parentCategories']);
        Route::get('/categories/stats', [CategoryController::class, 'stats']);
        Route::get('/categories', [CategoryController::class, 'index']);
        Route::get('/categories/{category}', [CategoryController::class, 'show']);
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::post('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
        Route::patch('/categories/{category}/toggle', [CategoryController::class, 'toggle']);
        Route::get('/categories/{category}/translations', [CategoryController::class, 'translations']);

        // Products
        Route::get('/products/stats', [ProductController::class, 'stats']);
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/{product}', [ProductController::class, 'show']);
        Route::post('/products', [ProductController::class, 'store']);
        Route::post('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);
        Route::patch('/products/{product}/toggle', [ProductController::class, 'toggle']);
        Route::get('/products/{product}/translations', [ProductController::class, 'translations']);

        // Orders
        Route::get('/orders/stats', [OrderController::class, 'stats']);
        Route::get('/orders/export', [OrderController::class, 'export']);
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);
        Route::patch('/orders/{order}/note', [OrderController::class, 'updateNote']);
        Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel']);
        Route::delete('/orders/{order}', [OrderController::class, 'destroy']);

        // Feedback
        Route::get('/feedback/stats', [FeedbackController::class, 'stats']);
        Route::get('/feedback/pending', [FeedbackController::class, 'pending']);
        Route::get('/feedback/resolved', [FeedbackController::class, 'resolved']);
        Route::get('/feedback', [FeedbackController::class, 'index']);
        Route::get('/feedback/{feedback}', [FeedbackController::class, 'show']);
        Route::patch('/feedback/{feedback}/resolve', [FeedbackController::class, 'resolve']);
        Route::patch('/feedback/{feedback}/unresolve', [FeedbackController::class, 'unresolve']);
        Route::delete('/feedback/{feedback}', [FeedbackController::class, 'destroy']);
    });

    // Client api
    Route::get('/categories', [CustomerController::class, 'categories']);
    Route::get('/categories/{category}/products', [CustomerController::class, 'categoryProducts']);
    Route::get('/products', [CustomerController::class, 'products']);
    Route::get('/products/{product}', [CustomerController::class, 'showProduct']);
    Route::get('/products/{product}/ratings', [CustomerController::class, 'productRatings']);

    // Ratings
    Route::post('/products/{product}/rate', [CustomerController::class, 'rateProduct']);
    Route::delete('/products/{product}/rate', [CustomerController::class, 'deleteRating']);

    // Orders
    Route::post('/orders', [CustomerController::class, 'placeOrder']);
    Route::get('/orders', [CustomerController::class, 'myOrders']);
    Route::get('/orders/{order}', [CustomerController::class, 'showOrder']);
    Route::patch('/orders/{order}/cancel', [CustomerController::class, 'cancelOrder']);
});
