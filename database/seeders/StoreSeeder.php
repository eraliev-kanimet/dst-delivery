<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $email = fake()->email;

        $owner = User::updateOrCreate([
            'email' => $email,
        ], [
            'email' => $email,
            'role_id' => 2,
            'name' => fake()->userName,
            'password' => Hash::make('password')
        ]);

        $store = fake()->company;

        Store::updateOrCreate([
            'name' => $store,
            'user_id' => $owner->id,
        ], [
            'name' => $store,
            'user_id' => $owner->id,
            'fallback_locale' => 'en',
            'locales' => ['en', 'ru'],
            'categories' => [1, 2, 3]
        ]);
    }
}
