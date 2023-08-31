<?php

namespace Database\Seeders\Traits;

use App\Models\Category;
use App\Models\Image;
use App\Models\Store;

trait ProductCategorySeeder
{
    use ProductProductSeeder;

    protected function create(Store $store, array $data, ?Category $parent = null): void
    {
        $categoryData = [
            'name' => $data['name'],
            'store_id' => $store->id,
        ];

        if ($parent?->id) {
            $categoryData['category_id'] = $parent->id;
        }

        $category = Category::firstOrCreate($categoryData, array_merge($categoryData, [
            'description' => $data['description'],
            'preview' => 1
        ]));

        if (!$category->images) {
            $category->images()->save(new Image(['values' => fakeImages('categories')]));
        }

        if (isset($data['children'])) {
            foreach ($data['children'] as $child) {
                $this->create($store, $child, $category);
            }
        }

        if (isset($data['products'])) {
            foreach ($data['products'] as $product) {
                $this->product($store, $product, $category->id);
            }
        }
    }
}
