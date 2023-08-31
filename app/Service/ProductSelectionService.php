<?php

namespace App\Service;

use App\Models\Product;
use App\Models\Selection;
use Illuminate\Database\Eloquent\Builder;

class ProductSelectionService
{
    public static function new(): static
    {
        return new self;
    }

    public function getSelectSelection(int $store_id, string $fallback_locale): array
    {
        $locale = config('app.locale');

        $products = Product::with([
            'content_en:product_id,name',
            'content_ru:product_id,name',
            'selections:id,product_id,properties,quantity,price,is_available',
        ])
            ->whereStoreId($store_id)
            ->whereHas('selections', function (Builder $query) {
                $query->whereNot('price', 0)->whereNot('quantity', 0);
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

    public function getPropertiesToString(array|null $properties, string $locale, string $fallback_locale, $limit = 10): string
    {
        $attributes = '';

        $count = 0;
        foreach ($properties ?? [] as $attribute) {
            if ($count == $limit) {
                break;
            }

            if ($attribute['type'] == 1) {
                $value = $attribute['value' . $attribute['type']][$locale];

                if (is_null($value)) {
                    $value = $attribute['value' . $attribute['type']][$fallback_locale];
                }
            } else {
                $value = $attribute['value' . $attribute['type']];
            }

            $attributes .= __('common.attributes.' . $attribute['attribute']) . ': ' . $value . ', ';

            $count++;
        }

        return trim($attributes, ', ');
    }

    public function creatingProductForOrder(Selection $selection): array
    {
        $product = $selection->product;

        $contents = [];

        foreach (array_keys(config('app.locales')) as $locale) {
            if ($product->{"content_$locale"}) {
                $contents["content_$locale"] = [
                    'name' => $product->{"content_$locale"}->name,
                    'description' => $product->{"content_$locale"}->description,
                ];
            }
        }

        $attributes = [];

        foreach ($product->attr as $attribute) {
            $attributes[$attribute->attr_key_id] = [
                'attribute' => $attribute->attr_key_id,
                'attribute_slug' => $attribute->attrKey->slug,
                'translatable' => $attribute->attrKey->translatable,
                'name' => $attribute->attrKey->name,
                'value' => $attribute->value,
            ];
        }

        foreach ($selection->attr as $attribute) {
            $attributes[$attribute->attr_key_id] = [
                'attribute' => $attribute->attr_key_id,
                'attribute_slug' => $attribute->attrKey->slug,
                'translatable' => $attribute->attrKey->translatable,
                'name' => $attribute->attrKey->name,
                'value' => $attribute->value,
            ];
        }

        return [
            'product_id' => $product->id,
            'selection_id' => $selection->id,
            'category' => [
                'id' => $product->category->id,
                'name' => $product->category->name,
            ],
            ...$contents,
            'images' => array_merge($product->images->values ?? [], $selection->images ?? []),
            'attributes' => $attributes,
        ];
    }
}
