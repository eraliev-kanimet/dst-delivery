<?php

namespace App\Service;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Selection;
use Illuminate\Database\Eloquent\Builder;

class ProductSelectionService
{
    public static function new(): static
    {
        return new self;
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

    public function getSelectSelection(int $store_id, string $fallback_locale): array
    {
        $locale = config('app.locale');

        $products = Product::with([
            'content_en:product_id,name',
            'content_ru:product_id,name',
            'selections.attr.attrKey',
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
                $selection_name = $name;

                $selection_name .= ': ' . __('common.price') . ' ' . $selection->price;
                $selection_name .= ', ' . __('common.quantity') . ' ' . $selection->quantity . ', ';

                foreach ($selection->attr->slice(0, 7) as $attribute) {
                    $key = $attribute->attrKey->name[$locale] ?? $attribute->attrKey->name[$fallback_locale];

                    if ($attribute->attrKey->translatable) {
                        $value = $attribute->value[$locale] ?? $attribute->value[$fallback_locale];
                    } else {
                        $value = $attribute->value['default'];
                    }

                    $selection_name .= $key . ': ' . $value . ', ';
                }

                $array[$selection->id] = trim($selection_name, ', ');
            }
        }

        return $array;
    }

    public function getOrderItemProduct(OrderItem $orderItem, string $fallback_locale): string
    {
        $locale = config('app.locale');

        $product = $orderItem->getProduct();

        $selection = Selection::find($product['selection_id']);

        if ($selection) {
            $product = $selection->product;

            if ($product->{"content_$locale"}) {
                $name = $product->{"content_$locale"}->name;
            } else {
                $name = $product->{"content_$fallback_locale"}->name;
            }

            $name .= ' - ';

            foreach ($selection->attr->slice(0, 10) as $attribute) {
                $key = $attribute->attrKey->name[$locale] ?? $attribute->attrKey->name[$fallback_locale];

                if ($attribute->attrKey->translatable) {
                    $value = $attribute->value[$locale] ?? $attribute->value[$fallback_locale];
                } else {
                    $value = $attribute->value['default'];
                }

                $name .= $key . ': ' . $value . ', ';
            }
        } else {
            if (isset($product["content_$locale"])) {
                $name = $product["content_$locale"]['name'];
            } else {
                $name = $product["content_$fallback_locale"]['name'];
            }

            $name .= ' - ';

            if (is_array($product['attributes'])) {
                foreach ($product['attributes'] as $attribute) {
                    $key = $attribute['name'][$locale] ?? $attribute['name'][$fallback_locale];

                    if ($attribute['translatable']) {
                        $value = $attribute['value'][$locale] ?? $attribute['value'][$fallback_locale];
                    } else {
                        $value = $attribute['value']['default'];
                    }

                    $name .= $key . ': ' . $value . ', ';
                }
            }
        }

        return trim($name, ', ');
    }
}
