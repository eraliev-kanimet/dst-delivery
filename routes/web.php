<?php

use Illuminate\Support\Facades\Route;

Route::redirect('login', 'admin/login', 301)->name('login');
