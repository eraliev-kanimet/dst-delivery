<?php

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\VerificationController;
use Illuminate\Support\Facades\Route;

Route::post('verification/phone/sms', [VerificationController::class, 'sendingSmsCodeToPhone'])
    ->name('verification.phone.sms');

Route::get('orders/info', [OrderController::class, 'info'])->name('orders.info');
