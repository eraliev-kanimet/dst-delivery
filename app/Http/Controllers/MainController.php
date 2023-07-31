<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;

class MainController extends Controller
{
    public function setLocale(string $locale)
    {
        App::setLocale($locale);

        session(['locale' => $locale]);

        return redirect()->back();
    }
}
