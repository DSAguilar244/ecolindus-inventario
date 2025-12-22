<?php

namespace Database\Factories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition()
    {
        return [
            'customer_id' => null, // set in test
            'user_id' => null, // set in test
            'invoice_number' => Str::upper('INV-'.$this->faker->unique()->numerify('##########')),
            'date' => now(),
            'subtotal' => 0,
            'tax_total' => 0,
            'total' => 0,
            'status' => Invoice::STATUS_EMITIDA,
            'cancelled_by' => null,
            'cancelled_at' => null,
            'notes' => $this->faker->sentence,
        ];
    }
}
