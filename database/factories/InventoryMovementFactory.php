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
            'type' => ($this->faker ? $this->faker->randomElement(['entrada', 'salida', 'dañado', 'devuelto']) : 'entrada'),
            'quantity' => ($this->faker ? $this->faker->numberBetween(1, 10) : 1),
            'reason' => ($this->faker ? $this->faker->sentence : 'Ingreso inicial'),
            'supplier_id' => \App\Models\Supplier::factory(),
            'user_id' => 1, // Asumimos que el usuario admin ya existe
        ];
    }
}
