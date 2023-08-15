<?php

use App\Http\Controllers\Api\Auth\CustomerAuthController;
use App\Http\Controllers\Api\Store\BannerController;
use App\Http\Controllers\Api\Store\CategoryController;
use App\Http\Controllers\Api\Store\OrderController;
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

Route::middleware('auth:customer')->group(function () {
    Route::apiResource('orders', OrderController::class)->except('destroy');

    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('cancel/{order}', [OrderController::class, 'cancel'])->name('cancel');
        Route::get('product/add', [OrderController::class, 'productAdd'])->name('product.add');
        Route::get('product/remove', [OrderController::class, 'productRemove'])->name('product.remove');
    });
});
