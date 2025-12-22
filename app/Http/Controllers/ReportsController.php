<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReportsController extends Controller
{
    public function monthly(Request $request)
    {
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        if (! $dateFrom || ! $dateTo) {
            // default to current month
            $dateFrom = now()->startOfMonth()->toDateString();
            $dateTo = now()->endOfMonth()->toDateString();
        }

        $invoices = Invoice::with('payment')
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->get();

        $totalSales = $invoices->sum('total');
        $totalIva = $invoices->sum('tax_total');
        $byCash = $invoices->sum(fn($inv) => $inv->payment?->cash_amount ?? 0);
        $byTransfer = $invoices->sum(fn($inv) => $inv->payment?->transfer_amount ?? 0);
        $countEmitted = $invoices->where('status', Invoice::STATUS_EMITIDA)->count();
        $countPending = $invoices->where('status', Invoice::STATUS_PENDIENTE)->count();

        return view('reports.monthly', compact('dateFrom','dateTo','totalSales','totalIva','byCash','byTransfer','countEmitted','countPending'));
    }

    public function export(Request $request)
    {
        $format = $request->query('format', 'pdf');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        if (! $dateFrom || ! $dateTo) {
            $dateFrom = now()->startOfMonth()->toDateString();
            $dateTo = now()->endOfMonth()->toDateString();
        }

        $invoices = Invoice::with('payment')
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->get();

        $totalSales = $invoices->sum('total');
        $totalIva = $invoices->sum('tax_total');
        $byCash = $invoices->sum(fn($inv) => $inv->payment?->cash_amount ?? 0);
        $byTransfer = $invoices->sum(fn($inv) => $inv->payment?->transfer_amount ?? 0);
        $countEmitted = $invoices->where('status', Invoice::STATUS_EMITIDA)->count();
        $countPending = $invoices->where('status', Invoice::STATUS_PENDIENTE)->count();


        if ($format === 'excel') {
            $filename = 'reporte-mensual-' . now()->format('Ymd') . '.csv';
            $callback = function () use ($invoices, $totalSales, $totalIva, $byCash, $byTransfer, $countEmitted, $countPending) {
                $out = fopen('php://output', 'w');
                // Add BOM for UTF-8 to help Excel detect encoding
                fwrite($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
                fputcsv($out, ['Fecha', 'Número', 'Cliente', 'Total', 'IVA', 'Efectivo', 'Transferencia', 'Estado']);

                foreach ($invoices as $inv) {
                    fputcsv($out, [
                        $inv->date,
                        $inv->number ?? $inv->id,
                        $inv->customer?->name ?? $inv->customer_name ?? '',
                        number_format($inv->total, 2, '.', ''),
                        number_format($inv->tax_total, 2, '.', ''),
                        number_format($inv->payment?->cash_amount ?? 0, 2, '.', ''),
                        number_format($inv->payment?->transfer_amount ?? 0, 2, '.', ''),
                        $inv->status,
                    ]);
                }

                // Totals summary
                fputcsv($out, []);
                fputcsv($out, ['', '', 'Total ventas', number_format($totalSales, 2, '.', '')]);
                fputcsv($out, ['', '', 'Total IVA', number_format($totalIva, 2, '.', '')]);
                fputcsv($out, ['', '', 'Efectivo', number_format($byCash, 2, '.', '')]);
                fputcsv($out, ['', '', 'Transferencia', number_format($byTransfer, 2, '.', '')]);

                fclose($out);
            };

            return response()->streamDownload($callback, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        // PDF export
        $pdf = Pdf::loadView('reports.monthly-pdf', compact('dateFrom', 'dateTo', 'totalSales', 'totalIva', 'byCash', 'byTransfer', 'countEmitted', 'countPending', 'invoices'));
        $pdf->setPaper('a4', 'landscape');
        return $pdf->download('reporte-mensual-' . now()->format('Ymd') . '.pdf');
    }

    /**
     * Queue a monthly export job (CSV/XLSX)
     */
    public function queue(Request $request, ReportService $reports)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        if (! $dateFrom || ! $dateTo) {
            $dateFrom = now()->startOfMonth()->toDateString();
            $dateTo = now()->endOfMonth()->toDateString();
        }

        $start = \Carbon\Carbon::parse($dateFrom);
        $year = $start->year;
        $month = $start->month;

        $token = $reports->queueMonthlyExport($year, $month, Auth::id());

        return response()->json(['token' => $token, 'message' => 'Tu exportación se está generando.']);
    }
}
