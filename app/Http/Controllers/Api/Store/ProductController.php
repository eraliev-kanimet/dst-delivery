<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $store = Store::current();

        ProductResource::$locale = config('app.locale');
        ProductResource::$fallback_locale = $store->fallback_locale;

        $products = Product::with(['category:id,name,category_id', 'selections', 'store:id', 'images'])
            ->whereStoreId($store->id)
            ->whereHas('selections')
            ->whereIn('category_id', $store->categories)
            ->orderBy('sorted', 'desc')
            ->orderBy('category_id', 'desc')
            ->paginate((int)$request->get('limit', 15));

        return ProductResource::collection($products);
    }

    public function show(Product $product)
    {
        ProductResource::$locale = config('app.locale');
        ProductResource::$fallback_locale = Store::current()->fallback_locale;

        return new ProductResource($product);
    }
}
