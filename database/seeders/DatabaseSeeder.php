<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Artisan::call('app:install');

        $this->call(StoreSeeder::class);
        $this->call(ProductSeeder::class);
        $this->call(BannerSeeder::class);
    }
}
