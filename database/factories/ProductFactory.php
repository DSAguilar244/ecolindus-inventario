<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Botellón 20L', 'Botella 500ml', 'Botella 600ml']),
            'category' => 'agua',
            'unit' => $this->faker->randomElement(['litros', 'botellones', 'unidades']),
            'stock' => $this->faker->numberBetween(10, 100),
            'min_stock' => $this->faker->numberBetween(5, 20),
        ];
    }
}
