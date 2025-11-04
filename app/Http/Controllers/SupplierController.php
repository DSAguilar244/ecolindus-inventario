<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class SupplierController extends Controller
{
    // Mostrar listado de proveedores
    public function index()
    {
        $suppliers = Supplier::orderBy('name')->paginate(10);
        return view('suppliers.index', compact('suppliers'));
    }

    //Exportar proveedores a PDF
    public function exportPdf()
    {
        $suppliers = \App\Models\Supplier::orderBy('name')->get();
        $pdf = Pdf::loadView('suppliers.pdf', compact('suppliers'));
        return $pdf->download('reporte_proveedores.pdf');
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
            'name' => 'sometimes|string|max:100',
            'contact' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:100',
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
