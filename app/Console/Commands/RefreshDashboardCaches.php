<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Invoice;

class RefreshDashboardCaches extends Command
{
    protected $signature = 'dashboard:refresh-caches';
    protected $description = 'Refresh cached dashboard aggregates (movementStats, sales_12_months, top_products).';

    public function handle(): int
    {
        $this->info('Refreshing dashboard caches...');

        // movementStats
        $movementStats = DB::table('inventory_movements')
            ->select('type', DB::raw('COUNT(*) as total'))
            ->groupBy('type')
            ->get();
        Cache::put('dashboard.movement_stats', $movementStats, 300);

        // sales 12 months
        $start = now()->subMonths(11)->startOfMonth();
        $end = now()->endOfMonth();

        $rows = Invoice::select(DB::raw("date_trunc('month', date) as month"), DB::raw('SUM(total) as total'))
            ->whereBetween('date', [$start, $end])
            ->where('status', Invoice::STATUS_EMITIDA)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy(function ($r) {
                return \Carbon\Carbon::parse($r->month)->format('Y-m');
            });

        $labels = [];
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $dt = now()->subMonths($i);
            $key = $dt->format('Y-m');
            $labels[] = $dt->format('M Y');
            $data[] = isset($rows[$key]) ? (float) $rows[$key]->total : 0.0;
        }
        Cache::put('dashboard.sales_12_months', [$labels, $data], 300);

        // top products
        $topProducts = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('products', 'invoice_items.product_id', '=', 'products.id')
            ->where('invoices.status', Invoice::STATUS_EMITIDA)
            ->select('products.name as product_name', DB::raw('SUM(invoice_items.quantity) as total_qty'), DB::raw('SUM(invoice_items.line_total) as revenue'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->limit(6)
            ->get();
        Cache::put('dashboard.top_products', $topProducts, 300);

        $this->info('Dashboard caches refreshed.');

        return 0;
    }
}
