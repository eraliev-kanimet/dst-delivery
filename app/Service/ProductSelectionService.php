<?php

namespace App\Service;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class ProductSelectionService
{
    public static function new(): static
    {
        return new self;
    }

    public function getSelectSelection($store_id, string $fallback_locale): array
    {
        $locale = config('app.locale');

        $products = Product::with([
            'content_en:product_id,name',
            'content_ru:product_id,name',
            'selections:id,product_id,properties,quantity,price,is_available',
        ])
            ->whereStoreId($store_id)
            ->whereHas('selections', function (Builder $query) {
                $query->whereNot('price', 0);
            })
            ->whereIsAvailable(true)
            ->get(['id']);

        $array = [];

        foreach ($products as $product) {
            if ($product->{"content_$locale"}) {
                $name = $product->{"content_$locale"}->name;
            } else {
                $name = $product->{"content_$fallback_locale"}->name;
            }

            foreach ($product->selections as $selection) {
                $selection_name = $name . ': ' . __('common.price') . ' ' . $selection->price;
                $selection_name .= ', ' . __('common.quantity') . ' ' . $selection->quantity;
                $selection_name .= ', ' . $this->getPropertiesToString($selection->properties, $locale, $fallback_locale);

                $array[$selection->id] = $selection_name;
            }
        }

        return $array;
    }

    protected function getPropertiesToString(array|null $properties, string $locale, string $fallback_locale): string
    {
        $attributes = '';

        foreach ($properties ?? [] as $attribute) {
            if ($attribute['type'] == 1) {
                $value = $attribute['value' . $attribute['type']][$locale];

                if (is_null($value)) {
                    $value = $attribute['value' . $attribute['type']][$fallback_locale];
                }
            } else {
                $value = $attribute['value' . $attribute['type']];
            }

            $attributes .= __('common.attributes.' . $attribute['attribute']) . ': ' . $value . ', ';
        }

        return trim($attributes, ', ');
    }

    public function getAttributes(array $properties, string $locale): array
    {
        $service = ProductService::new();

        $attributes = [];

        foreach ($properties as $attribute) {
            $attributes[] = [
                'attribute' => $attribute['attribute'],
                'name' => __('common.attributes.' . $attribute['attribute']),
                'value' => $service->getAttributeValue($attribute['type'], $attribute['value' . $attribute['type']], $locale)
            ];
        }

        return $attributes;
    }
}
