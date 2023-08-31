<?php

namespace App\Service;

use App\Models\Category;
use App\Models\Product;

class ProductService
{
    public static function new(): static
    {
        return new self;
    }

    public function getSelectProduct(?int $store_id = null): array
    {
        $locale = config('app.locale');
        $locales = config('app.locales');

        unset($locales[$locale]);

        $locales = array_keys(config('app.locales'));

        $products = Product::query()
            ->with(['content_en:product_id,name', 'content_ru:product_id,name'])
            ->whereIsAvailable(true);

        if ($store_id) {
            $products->whereStoreId($store_id);
        }

        $array = [];

        foreach ($products->get(['id']) as $product) {
            $name = '';

            if ($product->{"content_$locale"}) {
                $name = $product->{"content_$locale"}->name;
            } else {
                foreach ($locales as $altLocale) {
                    if ($product->{"content_$altLocale"}) {
                        $name = $product->{"content_$altLocale"}->name;
                        break;
                    }
                }
            }

            $array[$product->id] = $name;
        }

        return $array;
    }

    public function category(?Category $category, string $locale): ?array
    {
        if ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name[$locale],
                'category' => $this->category($category->category, $locale),
            ];
        }

        return null;
    }
}
