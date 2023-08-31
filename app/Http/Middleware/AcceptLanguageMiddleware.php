<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class AcceptLanguageMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $acceptLanguage = $request->header('Accept-Language');

        $locale = getSupportedLocale($acceptLanguage, array_keys(config('app.locales')));

        if ($locale) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
