<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{

    public function index()
    {
        $lowStockCount = Product::whereColumn('stock', '<', 'min_stock')->count();
        $totalProducts = Product::count();
        $totalMovements = InventoryMovement::count();
        $recentMovements = InventoryMovement::with(['product', 'user'])->latest()->take(5)->get();

        $stockData = Product::select('name', 'stock')
            ->orderBy('stock', 'asc')
            ->take(10)
            ->get();

        $movementStats = DB::table('inventory_movements')
            ->select('type', DB::raw('COUNT(*) as total'))
            ->groupBy('type')
            ->get();

        return view('dashboard', compact(
            'lowStockCount',
            'totalProducts',
            'totalMovements',
            'recentMovements',
            'stockData',
            'movementStats'
        ));
    }
}
