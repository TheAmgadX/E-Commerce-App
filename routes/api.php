<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;

// Public API routes

// Auth Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forget-password', [AuthController::class, 'forgetPassword']);
Route::post('/check-otp', [AuthController::class, 'checkOTP']);

// Product Endpoints    
Route::get('/products', [ProductController::class, 'products']);

// Categories Routes
Route::get('/categories', [CategoryController::class, 'categories']);

Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::patch('/reset-password', [UserController::class, 'resetPassword']);

    // User Profile
    Route::get('/profile', [UserController::class, 'profile']);
    Route::patch('/profile', [UserController::class, 'update']);
    Route::patch('/profile/password', [UserController::class, 'updatePassword']);
    Route::delete('/profile', [UserController::class, 'delete']);
    
    // Addresses
    Route::get('/addresses', [AddressController::class, 'addresses']);
    Route::post('/addresses', [AddressController::class, 'create']);
    Route::patch('/addresses/{address}', [AddressController::class, 'update']);
    Route::delete('/addresses/{address}', [AddressController::class, 'delete']);

    // Cart
    Route::get('/cart', [CartController::class, 'cartProducts']);
    Route::post('/cart', [CartController::class, 'addProduct']);
    Route::patch('/cart', [CartController::class, 'updateProductCount']);
    Route::delete('/cart', [CartController::class, 'deleteProduct']);

    // Orders 
    Route::get('/orders', [OrderController::class, 'getOrders']);
    Route::post('/orders', [OrderController::class, 'createOrder']);
    Route::delete('/orders/{order}', [OrderController::class, 'cancelOrder']);
    
    // Order Products
    Route::patch('/orders/{order}/products', [OrderController::class, 'updateProductQuantity']);
    Route::delete('/orders/{order}/products', [OrderController::class, 'removeProduct']);
});