<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

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

        $movementStats = DB::table('inventory_movements')
            ->select('type', DB::raw('COUNT(*) as total'))
            ->groupBy('type')
            ->get();

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
        $monthlySalesTotal = Invoice::whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->sum('total');

        $monthlyInvoices = Invoice::whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->count();

        $recentInvoices = Invoice::with(['customer', 'user'])->latest()->take(5)->get();

        $pendingInvoicesCount = Invoice::where('status', '!=', Invoice::STATUS_ANULADA)->count();

        // Series de ventas: últimos 12 meses (orden cronológico)
        $salesLabels = [];
        $salesData = [];
        for ($i = 11; $i >= 0; $i--) {
            $dt = now()->subMonths($i);
            $label = $dt->format('M Y');
            $salesLabels[] = $label;

            $sum = Invoice::whereYear('date', $dt->year)
                ->whereMonth('date', $dt->month)
                ->where('status', Invoice::STATUS_EMITIDA)
                ->sum('total');

            $salesData[] = (float) $sum;
        }

        // Top productos vendidos (por cantidad) en facturas emitidas
        $topProducts = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('products', 'invoice_items.product_id', '=', 'products.id')
            ->where('invoices.status', Invoice::STATUS_EMITIDA)
            ->select('products.name as product_name', DB::raw('SUM(invoice_items.quantity) as total_qty'), DB::raw('SUM(invoice_items.line_total) as revenue'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->limit(6)
            ->get();

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
