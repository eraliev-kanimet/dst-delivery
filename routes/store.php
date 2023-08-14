<?php

use App\Http\Controllers\Api\Auth\CustomerAuthController;
use App\Http\Controllers\Api\Store\BannerController;
use App\Http\Controllers\Api\Store\CategoryController;
use App\Http\Controllers\Api\Store\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('customers')->name('customers.')->group(function () {
    Route::post('register', [CustomerAuthController::class, 'register'])->name('register');
    Route::post('login', [CustomerAuthController::class, 'login'])->name('login');

    Route::middleware('auth:customer')->group(function () {
       Route::get('logout', [CustomerAuthController::class, 'logout'])->name('logout');
    });
});

Route::apiResource('categories', CategoryController::class)->only('index', 'show');
Route::apiResource('products', ProductController::class)->only('index', 'show');
Route::apiResource('banners', BannerController::class)->only('index');
