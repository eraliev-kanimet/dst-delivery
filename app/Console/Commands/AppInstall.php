<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class AppInstall extends Command
{
    protected $signature = 'app:install';

    public function handle(): void
    {
        if (! Role::count()) {
            $this->roles();

            User::updateOrCreate([
                'email' => 'admin@admin.com',
            ], [
                'email' => 'admin@admin.com',
                'role_id' => 1,
                'name' => 'Admin',
                'password' => Hash::make('password')
            ]);

            User::updateOrCreate([
                'email' => 'store@owner.com',
            ], [
                'email' => 'store@owner.com',
                'role_id' => 2,
                'name' => 'Store Owner',
                'password' => Hash::make('password')
            ]);

            $this->info('The command has successfully worked, the base roles and admin user have been created');
        } else {
            $this->error('This command has been run before! Please check the database!');
        }
    }

    protected function roles(): void
    {
        $roles = [
            'admin' => 'Admin',
            'store_owner' => 'Store Owner',
        ];

        foreach ($roles as $slug => $name) {
            Role::updateOrCreate([
                'slug' => $slug
            ], [
                'slug' => $slug,
                'name' => $name
            ]);
        }
    }
}
