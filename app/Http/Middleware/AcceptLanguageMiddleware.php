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

        $locale = $this->getSupportedLocale($acceptLanguage, array_keys(config('app.locales')));

        if ($locale) {
            App::setLocale($locale);
        }

        return $next($request);
    }

    private function getSupportedLocale($acceptLanguage, $supportedLocales): bool|string|null
    {
        $acceptLanguage = explode(',', $acceptLanguage);

        foreach ($acceptLanguage as $language) {
            $locale = strtok($language, ';');

            if (in_array($locale, $supportedLocales)) {
                return $locale;
            }
        }

        return null;
    }
}
