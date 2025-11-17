<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AddressController;

// Public API routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected API routes (require a valid Sanctum token)
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);

    // Profile
    Route::get('/profile', [UserController::class, 'profile']); // get all user data.
    Route::put('/profile', [UserController::class, 'update']);
    Route::put('/profile/password', [UserController::class, 'updatePassword']);

    // Address
    Route::get('/addresses', [AddressController::class, 'addresses']); // get addresses for specific user.
    Route::post('/address', [AddressController::class, 'create']);
    Route::put('/address/{address}', [AddressController::class, 'update']);
    Route::delete('/address/{address}', [AddressController::class, 'delete']);

    // Orders (We will build the controller in Phase 4)
    // Route::get('/orders', [OrderController::class, 'index']);
});