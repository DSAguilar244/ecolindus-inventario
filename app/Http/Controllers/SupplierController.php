<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    // Mostrar listado de proveedores
    public function index()
    {
        $suppliers = Supplier::orderBy('name')->paginate(10);

        return view('suppliers.index', compact('suppliers'));
    }

    // Exportar proveedores a PDF
    public function exportPdf()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $pdf = Pdf::loadView('suppliers.pdf', compact('suppliers'));

        // Configurar PDF similar a productos
        $pdf->setPaper('a4');
        $pdf->output();
        $canvas = $pdf->getDomPDF()->getCanvas();
        $canvas->page_text(550, 810, 'Página {PAGE_NUM} de {PAGE_COUNT}', null, 10);

        $filename = 'ECOLINDUS_Reporte_Proveedores_'.now()->format('Y-m-d_His').'.pdf';

        return $pdf->download($filename);
    }

    // Mostrar formulario de creación
    public function create()
    {
        return view('suppliers.create');
    }

    // Guardar nuevo proveedor
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'contact' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:100',
        ]);
        // avoid duplicates: check name or email
        $exists = null;
        if (! empty($validated['email'])) {
            $exists = Supplier::where('email', $validated['email'])->first();
        }
        if (! $exists) {
            $exists = Supplier::where('name', $validated['name'])->first();
        }
        if ($exists) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Proveedor ya existe', 'supplier' => $exists], 409);
            }

            return redirect()->route('suppliers.index')->with('error', 'Proveedor ya existe');
        }

        Supplier::create($validated);

        return redirect()->route('suppliers.index')->with('success', 'Proveedor creado correctamente.');
    }

    // Mostrar formulario de edición
    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    // Actualizar proveedor
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:100|unique:suppliers,name,'.$supplier->id,
            'contact' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:100|unique:suppliers,email,'.$supplier->id,
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.index')->with('success', 'Proveedor actualizado correctamente.');
    }

    // Eliminar proveedor
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Proveedor eliminado.');
    }
}
