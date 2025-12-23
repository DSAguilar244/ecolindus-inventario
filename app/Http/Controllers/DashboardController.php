<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $lowStockCount = Product::whereColumn('stock', '<', 'min_stock')->count();
        $totalProducts = Product::count();
        $totalMovements = InventoryMovement::count();
        $recentMovements = InventoryMovement::with(['product', 'user'])->latest()->take(5)->get();

        // Movimientos del mes actual (año + mes)
        $monthlyMovements = InventoryMovement::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        $stockData = Product::select('name', 'stock')
            ->orderBy('stock', 'asc')
            ->take(10)
            ->get();

        // Cache movement stats briefly to avoid repeated heavy aggregation on high traffic
        $movementStats = Cache::remember('dashboard.movement_stats', 120, function () {
            return DB::table('inventory_movements')
                ->select('type', DB::raw('COUNT(*) as total'))
                ->groupBy('type')
                ->get();
        });

        // Colores para el gráfico (mapeo por tipo)
        $typeColors = [
            'entrada' => 'rgba(40, 167, 69, 0.8)',
            'salida' => 'rgba(220, 53, 69, 0.8)',
            'dañado' => 'rgba(108, 117, 125, 0.8)',
            'devuelto' => 'rgba(23, 162, 184, 0.8)',
        ];

        $movementChartColors = $movementStats->map(function ($row) use ($typeColors) {
            return $typeColors[$row->type] ?? 'rgba(33, 37, 41, 0.8)';
        })->toArray();

        $movementChartBorderColors = $movementStats->map(function ($row) use ($typeColors) {
            // border color slightly darker
            $c = $typeColors[$row->type] ?? 'rgba(33, 37, 41, 1)';

            return str_replace('0.8', '1', $c);
        })->toArray();

        // Ventas: totales y serie temporal para últimos 12 meses
        $recentInvoices = Invoice::with(['customer', 'user'])->latest()->take(5)->get();

        $pendingInvoicesCount = Invoice::where('status', '!=', Invoice::STATUS_ANULADA)->count();

        // Series de ventas: últimos 12 meses (consulta agrupada para evitar N+1 y 12 queries)
        [$salesLabels, $salesData] = Cache::remember('dashboard.sales_12_months', 120, function () {
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

            return [$labels, $data];
        });

        // Top productos vendidos (por cantidad) en facturas emitidas
        // Cache top products short-term to reduce aggregation frequency
        $topProducts = Cache::remember('dashboard.top_products', 120, function () {
            return DB::table('invoice_items')
                ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->join('products', 'invoice_items.product_id', '=', 'products.id')
                ->where('invoices.status', Invoice::STATUS_EMITIDA)
                ->select('products.name as product_name', DB::raw('SUM(invoice_items.quantity) as total_qty'), DB::raw('SUM(invoice_items.line_total) as revenue'))
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('total_qty')
                ->limit(6)
                ->get();
        });

        return view('dashboard', compact(
            'lowStockCount',
            'totalProducts',
            'totalMovements',
            'recentMovements',
            'stockData',
            'movementStats',
            'monthlyMovements',
            'movementChartColors',
            'movementChartBorderColors',
            'monthlySalesTotal',
            'monthlyInvoices',
            'recentInvoices',
            'pendingInvoicesCount',
            'salesLabels',
            'salesData',
            'topProducts'
        ));
    }
}
