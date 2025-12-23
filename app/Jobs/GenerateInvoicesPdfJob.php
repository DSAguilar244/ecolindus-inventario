<?php

namespace App\Jobs;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class GenerateInvoicesPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function handle(): void
    {
        try {
            $query = Invoice::with('customer', 'items')->orderBy('date', 'desc');

            if (! empty($this->filters['customer_id'])) {
                $query->where('customer_id', $this->filters['customer_id']);
            }
            if (! empty($this->filters['date_from'])) {
                $query->whereDate('date', '>=', $this->filters['date_from']);
            }
            if (! empty($this->filters['date_to'])) {
                $query->whereDate('date', '<=', $this->filters['date_to']);
            }

            $invoices = [];
            $query->chunk(200, function ($rows) use (&$invoices) {
                foreach ($rows as $r) {
                    $invoices[] = $r; // accumulate; ok in worker memory for typical exports
                }
            });

            // Generate PDF from a simplified view 'invoices.export' expecting $invoices
            $pdf = Pdf::setOptions(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true])->loadView('invoices.export', compact('invoices'));
            $pdf->setPaper('a4', 'landscape');

            $filename = 'exports/invoices-export-'.now()->format('Ymd_His').'.pdf';
            $path = storage_path('app/'.$filename);
            if (! file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }
            file_put_contents($path, $pdf->output());

            Log::info('GenerateInvoicesPdfJob completed', ['path' => $filename]);
        } catch (\Throwable $e) {
            Log::error('GenerateInvoicesPdfJob failed: '.$e->getMessage());
            throw $e;
        }
    }
}
