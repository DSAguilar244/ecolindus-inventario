<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminOnlySeeder extends Seeder
{
    public function run(): void
    {
        // Create only the admin user
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@ecolindus.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
            'role' => 'admin',
        ]);
    }
}
