<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class AppInstall extends Command
{
    protected $signature = 'app:install';

    public function handle(): void
    {
        if (User::whereEmail('admin@admin.com')->exists()) {
            $this->error('This command has been run before! Please check the database!');
        } else {
            User::create([
                'email' => 'admin@admin.com',
                'role_id' => 1,
                'name' => 'Admin',
                'password' => Hash::make('password')
            ]);

            $this->info('The command has successfully worked, the base roles and admin user have been created');
        }
    }
}
