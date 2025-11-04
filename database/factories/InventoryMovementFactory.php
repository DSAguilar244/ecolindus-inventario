<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InventoryMovement>
 */
class InventoryMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => \App\Models\Product::factory(),
            'type' => $this->faker->randomElement(['entrada', 'salida', 'dañado', 'devuelto']),
            'quantity' => $this->faker->numberBetween(1, 10),
            'reason' => $this->faker->sentence,
            'supplier_id' => \App\Models\Supplier::factory(),
            'user_id' => 1, // Asumimos que el usuario admin ya existe
        ];
    }
}
