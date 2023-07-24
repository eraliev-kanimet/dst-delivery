<?php

namespace App\Http\Middleware;

use App\Models\Store;
use Closure;
use Illuminate\Http\Request;
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

        return $next($request);
    }
}
