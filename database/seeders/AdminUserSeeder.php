<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $email = env('ADMIN_EMAIL', 'admin@local');
        $password = env('ADMIN_PASSWORD', 'password');
        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin User',
                'password' => Hash::make($password),
                'is_admin' => true,
                'role' => 'admin',
            ]
        );
    }
}
