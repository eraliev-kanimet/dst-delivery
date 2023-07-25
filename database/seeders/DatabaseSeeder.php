<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (Role::count()) {
            $this->call(CategorySeeder::class);
            $this->call(StoreSeeder::class);
        } else {
            print "Run the app:install command first!\n";
        }
    }
}
