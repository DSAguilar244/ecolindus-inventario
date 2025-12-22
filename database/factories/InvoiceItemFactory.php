<?php

namespace Database\Factories;

use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition()
    {
        $unitPrice = $this->faker->randomFloat(2, 10, 500);
        $quantity = $this->faker->numberBetween(1, 10);
        $taxRate = $this->faker->randomElement([0, 12, 15]);
        $lineTotal = $quantity * $unitPrice;

        return [
            'invoice_id' => null, // set in test
            'product_id' => null, // set in test
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'tax_rate' => $taxRate,
            'line_total' => $lineTotal,
        ];
    }
}
