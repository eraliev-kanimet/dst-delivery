<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (!Role::count()) {
            Artisan::call('app:install');
        }

        $this->call(StoreSeeder::class);
        $this->call(ProductSeeder::class);
    }
}
