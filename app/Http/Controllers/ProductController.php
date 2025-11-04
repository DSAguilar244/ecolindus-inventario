<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ProductController extends Controller
{
    // Mostrar listado de productos
    public function index()
    {
        $products = Product::orderBy('name')->paginate(10); // 10 por página
        return view('products.index', compact('products'));
    }

    //Exportar productos a PDF
    public function exportPdf()
    {
        $products = \App\Models\Product::orderBy('name')->get();
        $pdf = Pdf::loadView('products.pdf', compact('products'));
        return $pdf->download('reporte_productos.pdf');
    }


    // Mostrar formulario de creación
    public function create()
    {
        return view('products.create');
    }

    // Guardar nuevo producto
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'category' => 'required|string|max:50',
            'unit' => 'required|string|max:20',
            'stock' => 'nullable|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
        ]);

        Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Producto creado correctamente.');
    }

    // Mostrar formulario de edición
    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    // Actualizar producto
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'category' => 'sometimes|string|max:50',
            'unit' => 'sometimes|string|max:20',
            'stock' => 'sometimes|integer|min:0',
            'min_stock' => 'sometimes|integer|min:0',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Producto actualizado correctamente.');
    }

    // Eliminar producto
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Producto eliminado.');
    }
}
