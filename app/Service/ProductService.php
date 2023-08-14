<?php

namespace App\Service;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;

class ProductService
{
    protected array $attributes_type1 = [
        'size',
        'country_of_production',
        'neckline',
        'height_type',
        'insulation',
        'collection',
        'care',
        'lining_material',
        'color',
        'ram',
        'storage',
        'display',
        'memory',
        'camera',
        'height',
        'capacity',
        'temperature_zones',
        'max_load',
        'power',
        'pump_pressure',
        'suction_power',
        'bowl_capacity',
    ];

    protected array $attributes_type2 = [
        'size_on_model',
        'processor',
    ];

    public static function new(): static
    {
        return new self;
    }

    public function getAttributesName(): array
    {
        $attributes = [];

        foreach (array_merge($this->attributes_type1, $this->attributes_type2) as $attribute) {
            $attributes[$attribute] = __('common.attributes.' . $attribute);
        }

        return $attributes;
    }

    public function isAttributeType1($value): bool
    {
        return in_array($value, $this->attributes_type1);
    }

    public function isAttributeType2($value): bool
    {
        return in_array($value, $this->attributes_type2);
    }

    public function createAttributes(array $attributes, int $product_id): void
    {
        foreach ($attributes as $attribute) {
            $type = 0;

            if ($this->isAttributeType1($attribute['attribute'])) {
                $type = 1;
            } else if ($this->isAttributeType2($attribute['attribute'])) {
                $type = 2;
            }

            if ($type) {
                Attribute::create([
                    'product_id' => $product_id,
                    'type' => $type,
                    'attribute' => $attribute['attribute'],
                    'value' . $type => $attribute['value' . $type]
                ]);
            }
        }
    }

    public function getType(string $attribute): int
    {
        if (in_array($attribute, $this->attributes_type1)) {
            return 1;
        } elseif (in_array($attribute, $this->attributes_type2)) {
            return 2;
        }

        return 0;
    }

    public function getAttributeValue(int $type, array|string $value, string $locale)
    {
        return match ($type) {
            1 => $value[$locale],
            default => $value
        };
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
