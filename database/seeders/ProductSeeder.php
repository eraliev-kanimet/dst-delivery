<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\Content;
use App\Models\Selection;
use App\Models\Store;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    protected Store $store;

    public function run(): void
    {
        if (Store::whereFallbackLocale('en')->count()) {
            $this->store = Store::inRandomOrder()->whereFallbackLocale('en')->limit(1)->first();

            $data = json_decode(file_get_contents(storage_path('fake/json/products.json')), true);

            foreach ($data as $value) {
                $this->create($value);
            }
        }
    }

    protected function create(array $data, ?Category $parent = null): void
    {
        $categoryData = [
            'name' => $data['name'],
        ];

        if ($parent?->id) {
            $categoryData['category_id'] = $parent->id;
        }

        $category = Category::updateOrCreate($categoryData, array_merge($categoryData, [
            'description' => $data['description'],
            'preview' => 1
        ]));

        if (!$category->images) {
            $category->images()->save(new Image(['values' => fakeImages('categories')]));
        }

        if (isset($data['children'])) {
            foreach ($data['children'] as $child) {
                $this->create($child, $category);
            }
        }

        if (isset($data['products'])) {
            foreach ($data['products'] as $product) {
                $this->product($product, $category->id);
            }
        }
    }

    protected function product(array $data, int $category_id): void
    {
        if (!Content::whereName($data['name']['en'])->whereLocale('en')->exists()) {
            $product = Product::create([
                'category_id' => $category_id,
                'store_id' => $this->store->id,
                'preview' => 1,
            ]);

            foreach ($data['attributes'] as $typeName => $attributes) {
                $type = 0;

                if ($typeName == 'type1') {
                    $type = 1;
                } elseif ($typeName == 'type2') {
                    $type = 2;
                }

                if ($type) {
                    foreach ($attributes as $attribute => $value) {
                        Attribute::create([
                            'product_id' => $product->id,
                            'type' => $type,
                            'attribute' => $attribute,
                            'value' . $type => $value
                        ]);
                    }
                }
            }

            foreach ($this->store->locales as $locale) {
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

            if (!$product->selections->count()) {
                foreach ($data['selections'] as $selection) {
                    $attributesArray = [];

                    foreach ($selection as $typeName => $attributes) {
                        $type = 0;

                        if ($typeName == 'type1') {
                            $type = 1;
                        } elseif ($typeName == 'type2') {
                            $type = 2;
                        }

                        if ($type) {
                            foreach ($attributes as $attribute => $value) {
                                $attributesArray[] = [
                                    'type' => $type,
                                    'attribute' => $attribute,
                                    'value' . $type => $value
                                ];
                            }
                        }
                    }

                    $selection = [
                        'quantity' => rand(5, 10),
                        'price' => rand(200, 300),
                        'images' => fakeImages('products'),
                        'properties' => $attributesArray
                    ];

                    $product->selections()->save(new Selection($selection));
                }
            }
        }
    }
}
