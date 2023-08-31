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

        $categories = $store->categories()->with([
            'category',
            'categories',
            'images',
            'products',
        ])->get();

        return CategoryResource::collection($categories);
    }

    public function show(Category $category)
    {
        return new CategoryResource($category);
    }
}
