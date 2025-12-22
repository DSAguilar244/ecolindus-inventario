<?php

namespace App\Services;

use App\Models\CashSession;
use App\Models\Invoice;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CashSessionService
{
    // Cache tag and TTL
    public const CACHE_TAG = 'cash_summary';
    public const CACHE_TTL = 60; // minutes

    public function getSummaryForUser(int $userId, CashSession $session = null): array
    {
        $sessionId = $session?->id ?? 'active_' . $userId;
        $cacheKey = "cash_summary:{$userId}:{$sessionId}";

        return Cache::tags([self::CACHE_TAG, "user:{$userId}"])->remember($cacheKey, now()->addMinutes(self::CACHE_TTL), function () use ($userId, $session) {
            $session = $session ?? CashSession::where('user_id', $userId)->where('status', 'open')->first();
            if (! $session) {
                return ['session' => null, 'totals' => [], 'invoices' => []];
            }

            $invoices = Invoice::where('user_id', $userId)
                ->where('status', Invoice::STATUS_EMITIDA)
                ->whereBetween('date', [$session->opened_at, now()])
                ->with(['customer', 'payment'])
                ->get();

            $totalCash = 0.0;
            $totalTransfer = 0.0;
            $totalInvoices = 0.0;
            $totalSubtotal = 0.0;
            $totalTax = 0.0;

            $invoiceList = [];
            foreach ($invoices as $invoice) {
                $paymentCash = $invoice->payment->cash_amount ?? 0;
                $paymentTransfer = $invoice->payment->transfer_amount ?? 0;

                $totalCash += (float) $paymentCash;
                $totalTransfer += (float) $paymentTransfer;
                $totalInvoices += (float) $invoice->total;
                $totalSubtotal += (float) $invoice->subtotal;
                $totalTax += (float) $invoice->tax_total;

                $invoiceList[] = [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'customer' => $invoice->customer?->first_name . ' ' . $invoice->customer?->last_name,
                    'subtotal' => $invoice->subtotal,
                    'tax' => $invoice->tax_total,
                    'total' => $invoice->total,
                    'payment' => [
                        'cash' => (float) ($paymentCash ?? 0),
                        'transfer' => (float) ($paymentTransfer ?? 0),
                    ],
                ];
            }

            return [
                'session' => [
                    'id' => $session->id,
                    'user_id' => $session->user_id,
                    'opened_at' => $session->opened_at,
                    'opening_amount' => (float) $session->opening_amount,
                    'total_invoiced' => (float) ($session->total_invoiced ?? 0),
                    'total_cash' => (float) ($session->total_cash ?? $totalCash),
                    'total_transfer' => (float) ($session->total_transfer ?? $totalTransfer),
                    'expected_closing' => (float) ($session->expected_closing ?? round($totalCash + $totalTransfer,2)),
                    'reported_closing_amount' => (float) ($session->reported_closing_amount ?? 0),
                    'difference' => is_null($session->difference) ? null : (float) $session->difference,
                ],
                'totals' => [
                    'invoices_count' => count($invoiceList),
                    'subtotal' => round($totalSubtotal, 2),
                    'tax' => round($totalTax, 2),
                    'total_invoices' => round($totalInvoices, 2),
                    'total_cash' => round($totalCash, 2),
                    'total_transfer' => round($totalTransfer, 2),
                    'expected_closing' => round($totalCash + $totalTransfer, 2),
                ],
                'invoices' => $invoiceList,
            ];
        });
    }

    public function invalidateSummaryForUser(int $userId): void
    {
        try {
            Cache::tags([self::CACHE_TAG, "user:{$userId}"])->flush();
        } catch (\Exception $e) {
            Log::warning('Could not flush cache for user '.$userId.': '.$e->getMessage());
        }
    }

    public function markIdempotency(string $key): void
    {
        try {
            // Prefer persistent idempotency when available
            if (class_exists(\App\Models\IdempotencyKey::class)) {
                \App\Models\IdempotencyKey::firstOrCreate(['key' => $key]);
                return;
            }
            Cache::put('idempotency:'.$key, true, now()->addMinutes(30));
        } catch (\Exception $e) {
            Log::warning('Could not set idempotency key '.$key);
        }
    }

    public function isIdempotentProcessed(string $key): bool
    {
        try {
            if (class_exists(\App\Models\IdempotencyKey::class)) {
                return \App\Models\IdempotencyKey::where('key', $key)->exists();
            }
            return Cache::get('idempotency:'.$key, false) === true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
