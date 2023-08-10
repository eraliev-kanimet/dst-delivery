<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Store;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Store::all() as $store) {
            for ($i = 0; $i < 5; $i++) {
                Banner::create([
                    'store_id' => $store->id,
                    'name' => fake()->name,
                    'image' => fakeImage('banners'),
                    'type' => 'url',
                    'type_value' => fake()->url,
                    'start_date' => now(),
                    'end_date' => now()->addMonth(),
                    'active' => true,
                ]);
            }
        }
    }
}
