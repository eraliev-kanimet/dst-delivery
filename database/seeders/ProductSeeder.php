<?php

namespace Database\Seeders;

use App\Models\AttrKey;
use App\Models\Store;
use Database\Seeders\Traits\ProductAttributeSeeder;
use Database\Seeders\Traits\ProductCategorySeeder;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    use ProductAttributeSeeder, ProductCategorySeeder;

    protected Store $store;

    public function run(): void
    {
        $store = Store::inRandomOrder()->whereFallbackLocale('en')->limit(1)->first();

        if ($store && !AttrKey::count()) {
            $this->createAttributes($store);

            $this->store = $store;

            $data = json_decode(file_get_contents(storage_path('fake/json/products.json')), true);

            foreach ($data as $value) {
                $this->create($store, $value);
            }
        }
    }
}
