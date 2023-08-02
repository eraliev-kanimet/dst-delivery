<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Store;
use Illuminate\Contracts\Database\Eloquent\Builder;

class CategoryController extends Controller
{
    public function index()
    {
        $store = Store::current();

        CategoryResource::$locale = config('app.locale');
        CategoryResource::$fallback_locale = $store->fallback_locale;

        $categories = Category::with([
            'category',
            'categories',
            'images',
            'products' => function (Builder $query) use ($store) {
                $query->where('store_id', $store->id);
            }
        ])->whereIn('id', $store->categories ?? [])->get();

        return CategoryResource::collection($categories);
    }

    public function show(Category $category)
    {
        CategoryResource::$locale = config('app.locale');
        CategoryResource::$fallback_locale = Store::current()->fallback_locale;

        return new CategoryResource($category);
    }
}
