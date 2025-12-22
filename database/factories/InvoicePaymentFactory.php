<?php

namespace Database\Factories;

use App\Models\InvoicePayment;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoicePaymentFactory extends Factory
{
    protected $model = InvoicePayment::class;

    public function definition()
    {
        $cashAmount = $this->faker->randomFloat(2, 0, 1000);
        $transferAmount = $this->faker->randomFloat(2, 0, 1000);

        return [
            'invoice_id' => null, // set in test
            'cash_amount' => $cashAmount,
            'transfer_amount' => $transferAmount,
        ];
    }
}
