<?php

use Illuminate\Support\Facades\Route;

Route::redirect('login', 'admin/login', 301)->name('login');

Route::get('docs', fn () => view('swagger'));
