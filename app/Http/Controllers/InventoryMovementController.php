<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    // Exportar movimientos a PDF
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

        // Resumen rápido para mostrar en el PDF
        $summary = [
            'total' => $movements->count(),
            'entrada' => $movements->where('type', 'entrada')->count(),
            'salida' => $movements->where('type', 'salida')->count(),
            'dañado' => $movements->where('type', 'dañado')->count(),
            'devuelto' => $movements->where('type', 'devuelto')->count(),
        ];

        $pdf = Pdf::loadView('movements.pdf', compact('movements', 'summary'));

        // Configurar PDF (A4)
        $pdf->setPaper('a4');
        $pdf->output();

        // Agregar numeración de páginas usando una fuente compatible (DejaVu Sans)
        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->getCanvas();
        $fontMetrics = $dompdf->getFontMetrics();
        // Usar Helvetica (ya incluida en dompdf) para numeración y compatibilidad
        $font = $fontMetrics->get_font('Helvetica', 'normal');
        $canvas->page_text(520, 820, 'Página {PAGE_NUM} de {PAGE_COUNT}', $font, 10);

        return $pdf->download('ECOLINDUS_Reporte_Movimientos_'.now()->format('Y-m-d_His').'.pdf');
    }

    // Mostrar formulario de creación
    public function create(Request $request)
    {
        $products = Product::all();
        $suppliers = Supplier::all();
        $product = Product::find($request->product);
        $type = in_array($request->type, ['entrada', 'salida']) ? $request->type : null;

        return view('movements.create', compact('products', 'suppliers', 'product', 'type'));
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
