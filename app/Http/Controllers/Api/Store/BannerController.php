<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Models\Banner;
use App\Models\Store;

class BannerController extends Controller
{
    public function index()
    {
        BannerResource::$locale = config('app.locale');

        $now = now();

        $banners = Banner::whereStoreId(Store::current()->id)
            ->whereActive(true)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->orderBy('sorted', 'desc')
            ->get();

        return BannerResource::collection($banners);
    }
}
