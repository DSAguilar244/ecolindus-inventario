<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Services\InvoiceTotalsCalculator;

class PaymentTotalMatchesInvoice implements Rule
{
    protected $request;
    protected $messageText = 'La suma de efectivo y transferencia no coincide con el total de la factura.';

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function passes($attribute, $value)
    {
        $items = $this->request->input('items', []);
        if (!is_array($items) || empty($items)) {
            return false;
        }

        $calculator = new InvoiceTotalsCalculator();
        $totals = $calculator->calculate($items);

        $expectedTotal = round($totals['total'], 2);
        $cash = (float) ($this->request->input('cash_amount') ?? 0);
        $transfer = (float) ($this->request->input('transfer_amount') ?? 0);

        return round($cash + $transfer, 2) === $expectedTotal;
    }

    public function message()
    {
        return $this->messageText;
    }
}
