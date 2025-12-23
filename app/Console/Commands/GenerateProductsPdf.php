<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\GenerateProductsPdfJob;

class GenerateProductsPdf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:pdf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera un PDF del reporte de productos y lo guarda en storage/app/public';

    public function handle()
    {
        // Dispatch a job to generate the PDF in background via queue worker.
        GenerateProductsPdfJob::dispatch()->onQueue('default');

        $this->info('PDF generation dispatched to queue (GenerateProductsPdfJob).');

        return 0;
    }
}
