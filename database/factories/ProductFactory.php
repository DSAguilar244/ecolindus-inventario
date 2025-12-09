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
            'code' => $this->faker->unique()->bothify('P-####'),
            'name' => $this->faker->randomElement(['Botellón 20L', 'Botella 500ml', 'Botella 600ml']),
            'category_id' => null,
            'unit' => $this->faker->randomElement(['litros', 'botellones', 'unidades']),
            'stock' => $this->faker->numberBetween(10, 100),
            'min_stock' => $this->faker->numberBetween(5, 20),
            'price' => $this->faker->randomFloat(2, 1, 100),
            'tax_rate' => $this->faker->randomElement([0, 15]),
        ];
    }
}
