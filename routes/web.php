<?php

use App\Http\Controllers\MainController;
use Illuminate\Support\Facades\Route;

Route::redirect('login', 'admin/login', 301)->name('login');

Route::get('docs', fn () => view('swagger'));

Route::get('set/locale/{locale}', [MainController::class, 'setLocale'])
    ->whereIn('locale', array_keys(config('app.locales')))
    ->name('set.locale');
