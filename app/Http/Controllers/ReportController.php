<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function salesByCustomer(Request $request)
    {
        $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'export' => ['nullable', 'in:pdf'],
        ]);

        $from = $request->input('date_from') ? now()->parse($request->input('date_from'))->startOfDay() : null;
        $to = $request->input('date_to') ? now()->parse($request->input('date_to'))->endOfDay() : null;

        $query = Invoice::query();
        if ($from) {
            $query->where('date', '>=', $from);
        }
        if ($to) {
            $query->where('date', '<=', $to);
        }

        $results = $query
            ->selectRaw('customer_id, SUM(total) as total, COUNT(*) as invoices_count')
            ->groupBy('customer_id')
            ->with('customer')
            ->get()
            ->map(function ($r) {
                return [
                    'customer_id' => $r->customer_id,
                    'customer_name' => $r->customer ? ($r->customer->first_name.' '.($r->customer->last_name ?? '')) : 'N/A',
                    'total' => (float) $r->total,
                    'invoices_count' => (int) $r->invoices_count,
                ];
            });

        if ($request->input('export') === 'pdf') {
            $pdf = PDF::loadView('reports.sales_by_customer_pdf', [
                'results' => $results,
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
            ]);

            return $pdf->stream('sales_by_customer.pdf');
        }

        $customers = Customer::orderBy('first_name')->get();

        return view('reports.sales_by_customer', [
            'results' => $results,
            'customers' => $customers,
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ]);
    }

    public function salesByProduct(Request $request)
    {
        $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'export' => ['nullable', 'in:pdf'],
        ]);

        $from = $request->input('date_from') ? now()->parse($request->input('date_from'))->startOfDay() : null;
        $to = $request->input('date_to') ? now()->parse($request->input('date_to'))->endOfDay() : null;

        $query = \App\Models\InvoiceItem::query();
        $query->selectRaw('product_id, SUM(quantity) as quantity_sold, SUM(quantity * unit_price) as total')
            ->groupBy('product_id')
            ->with('product');

        if ($from) {
            $query->whereHas('invoice', function ($q) use ($from) {
                $q->where('date', '>=', $from);
            });
        }
        if ($to) {
            $query->whereHas('invoice', function ($q) use ($to) {
                $q->where('date', '<=', $to);
            });
        }

        $results = $query->get()->map(function ($r) {
            return [
                'product_id' => $r->product_id,
                'product_name' => $r->product ? ($r->product->name ?? $r->product->code ?? 'N/A') : 'N/A',
                'quantity_sold' => (float) $r->quantity_sold,
                'total' => (float) $r->total,
            ];
        });

        if ($request->input('export') === 'pdf') {
            $pdf = PDF::loadView('reports.sales_by_product_pdf', [
                'results' => $results,
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
            ]);

            return $pdf->stream('sales_by_product.pdf');
        }

        return view('reports.sales_by_product', [
            'results' => $results,
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ]);
    }
}
