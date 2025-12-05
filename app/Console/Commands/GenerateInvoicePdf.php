<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerateInvoicePdf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:pdf {id : Invoice ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera un PDF de la factura y lo guarda en storage/app/public';

    public function handle()
    {
        $id = $this->argument('id');
        $invoice = Invoice::with('items.product','customer')->find($id);

        if (! $invoice) {
            $this->error("Factura con id {$id} no encontrada.");
            return 1;
        }

        // Aseguramos opciones que permiten cargar recursos locales/remotos si es necesario
        $pdf = Pdf::setOptions(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true])->loadView('invoices.pdf', compact('invoice'));

        $output = $pdf->output();

        $path = storage_path("app/public/invoice-{$invoice->id}.pdf");

        // crear directorio si no existe
        if (! file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $output);

        $this->info("PDF generado: {$path}");

        return 0;
    }
}
