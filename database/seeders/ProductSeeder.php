<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\ProductContent;
use App\Models\ProductSelection;
use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

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
            $category->images()->save(new Image(['values' => $this->images('categories')]));
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
        if (!ProductContent::whereName($data['name']['en'])->whereLocale('en')->exists()) {
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
                    $product->{'content_' . $locale}()->save(new ProductContent([
                        'locale' => $locale,
                        'name' => $data['name'][$locale],
                        'description' => $data['description'][$locale],
                    ]));
                }
            }

            if (!$product->images) {
                $product->images()->save(new Image(['values' => $this->images('products')]));
            }

            if (!$product->selections->count()) {
                for ($i = 0; $i < rand(1, 2); $i++) {
                    $selection = [
                        'quantity' => rand(5, 10),
                        'price' => rand(200, 300),
                        'properties' => [
                            'ru' => [
                                'Цвет' => 'Черный',
                                'Размер' => 'M',
                                'Тип ростовки' => 'для невысоких',
                                'Утеплитель' => 'синтепон',
                                'Уход за вещами' => 'бережная стирка при 30 градусах',
                                'Коллекция' => 'Базовая коллекция',
                                'Материал подкладки' => 'синтепон',
                            ],
                            'en' => [
                                'Color' => 'Black',
                                'Size' => 'M',
                                'Height type' => 'for short',
                                'Insulation' => 'syntepon',
                                'Care' => 'gentle washing at 30 degrees',
                                'Collection' => 'Basic Collection',
                                'Lining material' => 'syntepon',
                            ],
                        ]
                    ];

                    $product->selections()->save(new ProductSelection($selection));
                }
            }
        }
    }

    protected function images(string $model): array
    {
        $images = [];

        for ($i = 0; $i < rand(1, 3); $i++) {
            $images[] = $this->image($model);
        }

        return $images;
    }

    protected function image(string $model): string
    {
        $dir = storage_path("app/public/$model");

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $image = rand(1, 30) . '.jpg';

        if (!file_exists("$dir/$image")) {
            File::copy(storage_path("fake/images/$image"), "$dir/$image");
        }

        return "$model/$image";
    }
}
