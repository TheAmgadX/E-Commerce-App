<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\CategoryController;

// Public API routes

// Auth Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forget-password', [AuthController::class, 'forgetPassword']);
Route::post('/check-otp', [AuthController::class, 'checkOTP']);

// categories routes
Route::get('/categories', [CategoryController::class, 'categories']);

Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // User Profile
    Route::get('/profile', [UserController::class, 'profile']); // get all user data.
    Route::put('/profile', [UserController::class, 'update']);
    Route::put('/profile/password', [UserController::class, 'updatePassword']);
    Route::delete('/profile', [UserController::class, 'delete']);
    Route::put('reset-password', [UserController::class, 'resetPassword']);
    
    // Address
    Route::get('/addresses', [AddressController::class, 'addresses']); // get addresses for specific user.
    Route::post('/address', [AddressController::class, 'create']);
    Route::put('/address/{address}', [AddressController::class, 'update']);
    Route::delete('/address/{address}', [AddressController::class, 'delete']);

    // Orders (We will build the controller in Phase 4)
    // Route::get('/orders', [OrderController::class, 'index']);
});