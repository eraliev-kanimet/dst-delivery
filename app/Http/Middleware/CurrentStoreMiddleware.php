<?php

namespace App\Http\Middleware;

use App\Http\Resources\BaseResource;
use App\Models\Store;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class CurrentStoreMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $storeUuid = $request->header('store-uuid');

        if (!$storeUuid) {
            return response()->json(errors('Store-UUID header is missing!'), 422);
        }

        $store = Store::where('uuid', $storeUuid)->first();

        if (!$store) {
            return response()->json(errors('Store not found!'), 422);
        }

        Store::setCurrent($store);

        $acceptLanguage = $request->header('Accept-Language');

        $locale = getSupportedLocale($acceptLanguage, $store->locales);

        if (is_null($locale)) {
            $locale = $store->fallback_locale;
        }

        App::setLocale($locale);

        BaseResource::$locale = $locale;

        return $next($request);
    }
}
