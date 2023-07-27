<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Store;

class CategoryController extends Controller
{
    public function index()
    {
        $store = Store::current();

        CategoryResource::$locale = config('app.locale');
        CategoryResource::$fallback_locale = $store->fallback_locale;

        $categories = Category::with(['category', 'categories', 'images'])
            ->whereIn('id',$store->categories ?? [])
            ->get();

        return CategoryResource::collection($categories);
    }

    public function show(Category $category)
    {
        CategoryResource::$locale = config('app.locale');
        CategoryResource::$fallback_locale = Store::current()->fallback_locale;

        return new CategoryResource($category);
    }
}
