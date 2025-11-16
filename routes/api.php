<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

// Public API routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// TODO: Add forgot password routes
// Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
// Route::post('/reset-password', [PasswordResetController::class, 'reset']);

// Protected API routes (require a valid Sanctum token)
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);

    // Profile
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    // Orders (We will build the controller in Phase 4)
    // Route::get('/orders', [OrderController::class, 'index']);
});