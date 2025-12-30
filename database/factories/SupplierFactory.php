<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Supplier>
 */
class SupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $rand = uniqid();
        return [
            'name' => ($this->faker ? $this->faker->unique()->company : 'Proveedor Demo ' . $rand),
            'contact' => ($this->faker ? $this->faker->phoneNumber : '0999999999'),
            'email' => ($this->faker ? $this->faker->unique()->companyEmail : 'proveedor' . $rand . '@demo.com'),
        ];
    }
}
