<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\ProductSelection;
use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ProductSeeder extends Seeder
{
    protected int $store_id;

    public function run(): void
    {
        if (Store::count()) {
            $this->store_id = Store::inRandomOrder()->limit(1)->first()->id;

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
            'description' => $data['description']
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
        $product = Product::updateOrCreate([
            'category_id' => $category_id,
            'store_id' => $this->store_id,
            'name' => $data['name'],
        ], [
            'category_id' => $category_id,
            'store_id' => $this->store_id,
            'name' => $data['name'],
            'description' => $data['description'],
            'properties' => $data['properties'],
        ]);

        if (!$product->images) {
            $product->images()->save(new Image(['values' => $this->images('products')]));
        }

        if (!$product->selections->count()) {
            for ($i = 0; $i < rand(2, 3); $i++) {
                $selection = [
                    'quantity' => rand(5, 10),
                    'price' => rand(200, 300),
                    'properties' => $data['properties'][0]['properties']
                ];

                $product->selections()->save(new ProductSelection($selection));
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

        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $image = rand(1, 30) . '.jpg';

        if (! file_exists("$dir/$image")) {
            File::copy(storage_path("fake/images/$image"), "$dir/$image");
        }

        return "$model/$image";
    }
}
