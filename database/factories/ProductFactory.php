<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Generator as Faker;

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
            'code' => ($this->faker ? $this->faker->unique()->bothify('P-####') : uniqid('P-')),
            'name' => ($this->faker ? $this->faker->randomElement(['Botellón 20L', 'Botella 500ml', 'Botella 600ml']) : 'Botellón 20L'),
            'category_id' => null,
            'unit' => ($this->faker ? $this->faker->randomElement(['litros', 'botellones', 'unidades']) : 'unidades'),
            'stock' => ($this->faker ? $this->faker->numberBetween(10, 100) : 10),
            'min_stock' => ($this->faker ? $this->faker->numberBetween(5, 20) : 5),
            'price' => ($this->faker ? $this->faker->randomFloat(2, 1, 100) : 10.00),
            'tax_rate' => ($this->faker ? $this->faker->randomElement([0, 15]) : 0),
        ];
    }
}
