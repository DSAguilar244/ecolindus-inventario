<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoicePayment;
use Illuminate\Http\Request;

class InvoicePaymentController extends Controller
{
    public function store(Request $request, Invoice $invoice)
    {
        $request->validate([
            'cash_amount' => 'required|numeric|min:0',
            'transfer_amount' => 'required|numeric|min:0',
        ]);

        $total = (float) $request->cash_amount + (float) $request->transfer_amount;
        $invoiceTotal = (float) $invoice->total;

        if (abs($total - $invoiceTotal) > 0.01) {
            return redirect()->back()->with('error', 'La suma de efectivo y transferencias no coincide con el total de la factura.');
        }

        $payment = InvoicePayment::updateOrCreate(
            ['invoice_id' => $invoice->id],
            [
                'cash_amount' => $request->cash_amount,
                'transfer_amount' => $request->transfer_amount,
            ]
        );

        $invoice->update(['payment_method' => 'mixed']);

        return redirect()->back()->with('success', 'Pago registrado correctamente.');
    }

    public function edit(Invoice $invoice)
    {
        $payment = $invoice->payment ?? new InvoicePayment();

        return view('invoices.payment_modal', compact('invoice', 'payment'));
    }
}
