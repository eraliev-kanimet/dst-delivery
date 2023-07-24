<?php

use App\Http\Controllers\Api\VerificationController;
use Illuminate\Support\Facades\Route;

Route::post('verification/phone/sms', [VerificationController::class, 'sendingSmsCodeToPhone'])->name('verification.phone.sms');
