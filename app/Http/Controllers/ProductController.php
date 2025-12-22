<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductController extends Controller
{
    // Mostrar listado de productos
    public function index()
    {
        $products = Product::with(['brand', 'categoryModel'])->orderBy('name')->paginate(10); // 10 por página

        return view('products.index', compact('products'));
    }

    // Exportar productos a PDF
    public function exportPdf()
    {
        $products = Product::with(['brand', 'categoryModel'])->orderBy('name')->get();

        // Mover cálculos costosos fuera de la vista
        $summary = [
            'total_products' => $products->count(),
            'critical' => Product::whereColumn('stock', '<', 'min_stock')->count(),
            'total_stock' => Product::sum('stock'),
            'categories' => Product::select('category')->distinct()->count(),
        ];

        $pdf = Pdf::loadView('products.pdf', compact('products', 'summary'));

        // Configurar el PDF
        $pdf->setPaper('a4');
        $pdf->output();
        $canvas = $pdf->getDomPDF()->getCanvas();

        // Agregar número de página
        $canvas->page_text(550, 810, 'Página {PAGE_NUM} de {PAGE_COUNT}', null, 10);

        return $pdf->download('ECOLINDUS_Reporte_Inventario_'.now()->format('Y-m-d_His').'.pdf');
    }

    // Mostrar formulario de creación
    public function create()
    {
        $brands = \App\Models\Brand::orderBy('name')->get();
        $categories = \App\Models\Category::orderBy('name')->get();

        return view('products.create', compact('brands', 'categories'));
    }

    // Guardar nuevo producto
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:50|unique:products,code',
            'name' => 'required|string|max:100',
            'category' => 'nullable|string|max:50',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'unit' => 'required|string|max:20',
            'stock' => 'nullable|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'price' => 'required|numeric|min:0',
            'tax' => 'nullable|in:0,15',
        ]);

        // If category_id provided, prefer it; otherwise use textual category
        if (! empty($validated['category_id'])) {
            // prefer category_id; but if DB still has 'category' column as NOT NULL,
            // set category string from the relation to keep compatibility until migration drops the column.
            $catModel = \App\Models\Category::find($validated['category_id']);
            if (Schema::hasColumn('products', 'category')) {
                $validated['category'] = $catModel?->name ?? '';
            } else {
                unset($validated['category']);
            }
        }

        $product = Product::create($validated);
        // If DB still has textual 'category' column and we set category, ensure the DB value exists
        if (Schema::hasColumn('products', 'category') && isset($validated['category'])) {
            $product->category = $validated['category'];
            $product->save();
        }

        return redirect()->route('products.index')->with('success', 'Producto creado correctamente.');
    }

    // Mostrar formulario de edición
    public function edit(Product $product)
    {
        $brands = \App\Models\Brand::orderBy('name')->get();
        $categories = \App\Models\Category::orderBy('name')->get();

        return view('products.edit', compact('product', 'brands', 'categories'));
    }

    // Actualizar producto
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'code' => 'sometimes|string|max:50|unique:products,code,'.$product->id,
            'name' => 'sometimes|string|max:100',
            'category' => 'nullable|string|max:50',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'unit' => 'sometimes|string|max:20',
            'stock' => 'sometimes|integer|min:0',
            'min_stock' => 'sometimes|integer|min:0',
            'price' => 'sometimes|numeric|min:0',
            'tax' => 'sometimes|in:0,15',
        ]);

        if (! empty($validated['category_id'])) {
            $catModel = \App\Models\Category::find($validated['category_id']);
            if (Schema::hasColumn('products', 'category')) {
                $validated['category'] = $catModel?->name ?? '';
            } else {
                unset($validated['category']);
            }
        }

        $product->update($validated);
        if (Schema::hasColumn('products', 'category') && isset($validated['category'])) {
            $product->category = $validated['category'];
            $product->save();
        }

        return redirect()->route('products.index')->with('success', 'Producto actualizado correctamente.');
    }

    // Eliminar producto
    public function destroy(Product $product)
    {
        // Prevent deleting a product that is referenced by invoice items
        if ($product->invoiceItems()->exists()) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'El producto está referenciado en facturas y no puede eliminarse.'], 409);
            }
            return redirect()->route('products.index')->with('error', 'El producto está referenciado en facturas y no puede eliminarse.');
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Producto eliminado.');
    }

    /**
     * AJAX search used for Select2 and barcode lookups
     */
    public function search(Request $request)
    {
        $q = $request->get('q');
        $query = Product::query();
        if ($q) {
            $driver = DB::getDriverName();
            $op = $driver === 'pgsql' ? 'ILIKE' : 'LIKE';
            $query->where(function ($qr) use ($q, $op) {
                $qr->where('code', $op, "%{$q}%")
                    ->orWhere('name', $op, "%{$q}%");
            });
        }

        $products = $query->orderBy('name')->limit(20)->get();
        $results = $products->map(function ($p) {
            return [
                'id' => $p->id,
                'text' => $p->name.' - '.($p->code ?? ''),
                'price' => (float) $p->price,
                'tax' => (int) ($p->tax ?? 0),
                'code' => $p->code,
            ];
        });

        return response()->json(['results' => $results]);
    }
}
