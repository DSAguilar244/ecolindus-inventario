<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(InventorySeeder::class);

        // Create a test user if not present
        \App\Models\User::updateOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => bcrypt('password'), 'role' => 'viewer']
        );

        // Seed invoices sample data
        $this->call(InvoiceSeeder::class);

        // Create Admin user for development
        $this->call(AdminUserSeeder::class);
    }
}
