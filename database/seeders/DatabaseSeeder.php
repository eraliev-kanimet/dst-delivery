<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (Role::count()) {
            $this->call(StoreSeeder::class);
            $this->call(ProductSeeder::class);
        } else {
            print "Run the app:install command first!\n";
        }
    }
}
