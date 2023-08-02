<?php

namespace App\Http\Resources;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryResource extends BaseResource
{
    /**
     * @var Category
     */
    public $resource;

    public function toArray(Request $request): array
    {
        $resource = $this->resource;
        $parent = null;

        $locale = self::$locale;
        $fallback_locale = self::$fallback_locale;

        if ($resource->category_id) {
            $parent = [
                'id' => $resource->category->id,
                'name' => $resource->category->name[$locale] ?? $resource->category->name[$fallback_locale],
            ];
        }

        return [
            'id' => $resource->id,
            'name' => $resource->name[$locale] ?? $resource->name[$fallback_locale],
            'description' => $resource->description[$locale] ?? $resource->description[$fallback_locale],
            'images' => $resource->getImages(),
            'parent' => $parent,
            'children' => self::collection($resource->categories),
            'products' => $this->getProductsCount($resource),
            'preview' => $resource->preview,
        ];
    }

    protected function getProductsCount(Category $category): int
    {
        $count = $category->products->count();

        foreach ($category->categories as $category) {
            $count += $this->getProductsCount($category);
        }

        return $count;
    }
}
