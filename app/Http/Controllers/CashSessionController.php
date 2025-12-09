<?php

namespace App\Http\Controllers;

use App\Models\CashSession;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashSessionController extends Controller
{
    public function open(Request $request)
    {
        $user = Auth::user();

        // Validar que no haya sesión abierta
        $activeSession = CashSession::where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        if ($activeSession) {
            return redirect()->back()->with('error', 'Ya existe una sesión de caja abierta.');
        }

        $request->validate([
            'opening_amount' => 'required|numeric|min:0',
        ]);

        $session = CashSession::create([
            'user_id' => $user->id,
            'opened_at' => now(),
            'opening_amount' => $request->opening_amount,
            'status' => 'open',
        ]);

        return redirect()->back()->with('success', 'Caja abierta correctamente.');
    }

    public function close(Request $request)
    {
        $user = Auth::user();

        $session = CashSession::where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        if (!$session) {
            return redirect()->back()->with('error', 'No existe una sesión de caja abierta.');
        }

        // Calcular totales de facturas emitidas en esta sesión
        $invoices = Invoice::where('user_id', $user->id)
            ->where('status', Invoice::STATUS_EMITIDA)
            ->whereBetween('date', [$session->opened_at, now()])
            ->with('payment')
            ->get();

        $totalCash = 0;
        $totalTransfer = 0;

        foreach ($invoices as $invoice) {
            if ($invoice->payment) {
                $totalCash += (float) $invoice->payment->cash_amount;
                $totalTransfer += (float) $invoice->payment->transfer_amount;
            }
        }

        // Set closing amount based on recorded payments within the session (cash + transfer)
        $calculatedClosing = round($totalCash + $totalTransfer, 2);

        // Optionally accept a reported closing amount from the UI for audit (not used to overwrite calculated closing)
        $reported = $request->input('reported_closing_amount');
        $notes = "Efectivo: {$totalCash}, Transferencias: {$totalTransfer}";
        if (!is_null($reported)) {
            $notes .= ", Reportado: {$reported}";
        }

        $session->update([
            'closed_at' => now(),
            'closing_amount' => $calculatedClosing,
            'status' => 'closed',
            'notes' => $notes,
        ]);

        return redirect()->back()->with('success', 'Caja cerrada correctamente.');
    }

    public function summary()
    {
        $user = Auth::user();

        $session = CashSession::where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        if (!$session) {
            return response()->json(['error' => 'No hay sesión activa'], 404);
        }

        $invoices = Invoice::where('user_id', $user->id)
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

        return response()->json([
            'session' => [
                'id' => $session->id,
                'user_id' => $session->user_id,
                'opened_at' => $session->opened_at,
                'opening_amount' => (float) $session->opening_amount,
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
        ]);
    }
}
