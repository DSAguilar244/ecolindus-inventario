<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class InventoryMovementController extends Controller
{
    // Mostrar listado de movimientos con filtros opcionales
    public function index(Request $request)
    {
        $query = InventoryMovement::with(['product', 'supplier', 'user'])->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $movements = $query->paginate(10)->withQueryString();

        return view('movements.index', compact('movements'));
    }

    //Exportar movimientos a PDF
    public function exportPdf(Request $request)
    {
        $query = InventoryMovement::with(['product', 'supplier', 'user'])->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $movements = $query->get();

        $pdf = Pdf::loadView('movements.pdf', compact('movements'));
        return $pdf->download('reporte_movimientos.pdf');
    }

    // Mostrar formulario de creación
    public function create()
    {
        $products = Product::all();
        $suppliers = Supplier::all();

        return view('movements.create', compact('products', 'suppliers'));
    }

    // Guardar nuevo movimiento
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:entrada,salida,dañado,devuelto',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
            'supplier_id' => 'nullable|exists:suppliers,id',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ($validated['type'] === 'salida' && $product->stock < $validated['quantity']) {
            return redirect()->back()->withErrors(['quantity' => 'Stock insuficiente para realizar la salida.']);
        }

        $movement = new InventoryMovement($validated);
        $movement->user_id = Auth::id() ?? 1; // fallback para trazabilidad si no hay login
        $movement->save();

        // Actualizar stock según tipo
        if ($validated['type'] === 'entrada') {
            $product->stock += $validated['quantity'];
        } elseif ($validated['type'] === 'salida') {
            $product->stock -= $validated['quantity'];
        }

        $product->save();

        return redirect()->route('movements.index')->with('success', 'Movimiento registrado correctamente.');
    }

    // Mostrar detalle (opcional en interfaz)
    public function show(InventoryMovement $movement)
    {
        $movement->load(['product', 'supplier', 'user']);
        return view('movements.show', compact('movement'));
    }

    // Eliminar movimiento
    public function destroy(InventoryMovement $movement)
    {
        $movement->delete();
        return redirect()->route('movements.index')->with('success', 'Movimiento eliminado.');
    }
}
