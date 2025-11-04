<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario admin
        $user = \App\Models\User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@ecolindus.com',
            'password' => bcrypt('admin123'),
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
