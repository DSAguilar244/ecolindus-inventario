<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;

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
        $products = Product::with(['brand', 'categoryModel'])->orderBy('name')->get();

        $summary = [
            'total_products' => $products->count(),
            'critical' => Product::whereColumn('stock', '<', 'min_stock')->count(),
            'total_stock' => Product::sum('stock'),
            'categories' => Product::select('category')->distinct()->count(),
        ];

        $pdf = Pdf::setOptions(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true])->loadView('products.pdf', compact('products', 'summary'));
        $output = $pdf->output();

        $path = storage_path('app/public/products-report.pdf');
        if (! file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        file_put_contents($path, $output);

        $this->info("PDF generado: {$path}");

        return 0;
    }
}
