<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'user:create-admin {email?} {password?}';

    protected $description = 'Create or update an admin user';

    public function handle()
    {
        $email = $this->argument('email') ?? env('ADMIN_EMAIL', 'admin@ecolindus.com');
        $password = $this->argument('password') ?? env('ADMIN_PASSWORD', 'admin123');

        $user = User::updateOrCreate(
            ['email' => $email],
            ['name' => 'Admin', 'password' => Hash::make($password), 'is_admin' => true, 'role' => 'admin']
        );

        $this->info('Admin user created/updated: '.$user->email);
        return 0;
    }
}
