<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or update admin user (idempotent)
        $adminEmail = env('ADMIN_EMAIL', 'admin@ecolindus.com');
        $user = \App\Models\User::updateOrCreate([
            'email' => $adminEmail,
        ], [
            'name' => 'Admin',
            'password' => bcrypt(env('ADMIN_PASSWORD', 'admin123')),
            'role' => 'admin',
            'is_admin' => true,
        ]);

        // Crear productos y proveedores
        $products = \App\Models\Product::factory(10)->create();
        $suppliers = \App\Models\Supplier::factory(5)->create();

        // Crear movimientos
        foreach ($products as $product) {
            \App\Models\InventoryMovement::factory()->create([
                'product_id' => $product->id,
                'supplier_id' => $suppliers->random()->id,
                'user_id' => $user->id,
            ]);
        }
    }
}
