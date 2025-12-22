<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    public function normalizePaymentAmounts(Invoice $invoice): bool
    {
        // Ensure payment sum equals invoice total
        $payment = $invoice->payment;
        if (! $payment) return false;

        $sum = (float) ($payment->cash_amount ?? 0) + (float) ($payment->transfer_amount ?? 0);
        if (round($sum, 2) !== round((float) $invoice->total, 2)) {
            return false;
        }

        return true;
    }

    public function logIssue(string $message, array $context = [])
    {
        Log::warning($message, $context);
    }
}
