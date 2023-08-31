<?php

namespace Database\Seeders\Traits;

use App\Models\AttrKey;
use App\Models\AttrValue;
use App\Models\AttrValueSelection;
use App\Models\Content;
use App\Models\Image;
use App\Models\Product;
use App\Models\Selection;
use App\Models\Store;

trait ProductProductSeeder
{
    protected function product(Store $store, array $data, int $category_id): void
    {
        if (!Content::whereName($data['name']['en'])->whereLocale('en')->exists()) {
            $product = Product::create([
                'category_id' => $category_id,
                'store_id' => $store->id,
                'preview' => 1,
            ]);

            foreach ($store->locales as $locale) {
                if (!$product->{'content_' . $locale}) {
                    $product->{'content_' . $locale}()->save(new Content([
                        'locale' => $locale,
                        'name' => $data['name'][$locale],
                        'description' => $data['description'][$locale],
                    ]));
                }
            }

            if (!$product->images) {
                $product->images()->save(new Image(['values' => fakeImages('products')]));
            }

            foreach ($data['attributes'] as $attributes) {
                foreach ($attributes as $attribute => $value) {
                    $attrKey = AttrKey::whereSlug($attribute)->first();

                    if ($attrKey) {
                        if (!$attrKey->translatable) {
                            $value = [
                                'default' => $value,
                            ];
                        }

                        AttrValue::firstOrCreate([
                            'attr_key_id' => $attrKey->id,
                            'product_id' => $product->id,
                        ], [
                            'attr_key_id' => $attrKey->id,
                            'product_id' => $product->id,
                            'value' => $value
                        ]);
                    }
                }
            }

            if (!$product->selections->count()) {
                foreach ($data['selections'] as $selection) {
                    $selection_id = Selection::create([
                        'product_id' => $product->id,
                        'quantity' => rand(5, 10),
                        'price' => rand(200, 300),
                        'images' => fakeImages('products'),
                    ])->id;

                    foreach ($selection as $attributes) {
                        foreach ($attributes as $attribute => $value) {
                            $attrKey = AttrKey::whereSlug($attribute)->first();

                            if ($attrKey) {
                                if (!$attrKey->translatable) {
                                    $value = [
                                        'default' => $value,
                                    ];
                                }

                                AttrValueSelection::firstOrCreate([
                                    'attr_key_id' => $attrKey->id,
                                    'product_id' => $product->id,
                                    'selection_id' => $selection_id,
                                ], [
                                    'attr_key_id' => $attrKey->id,
                                    'product_id' => $product->id,
                                    'value' => $value
                                ]);
                            }
                        }
                    }
                }
            }
        }
    }
}
