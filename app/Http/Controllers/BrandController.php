<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Models\Brand;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::orderBy('name')->paginate(20);

        return view('brands.index', compact('brands'));
    }

    public function create()
    {
        return view('brands.create');
    }

    public function store(StoreBrandRequest $request)
    {
        $data = $request->validated();

        // Avoid duplicates - if a brand with the same name already exists, return 409
        $exists = Brand::where('name', $data['name'])->first();
        if ($exists) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Marca ya existe', 'brand' => $exists], 409);
            }

            return redirect()->route('brands.index')->with('error', 'Marca con ese nombre ya existe');
        }

        $brand = Brand::create($data);
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['id' => $brand->id, 'text' => $brand->name, 'brand' => $brand], 201);
        }

        return redirect()->route('brands.index')->with('success', 'Marca creada');
    }

    public function edit(Brand $brand)
    {
        return view('brands.edit', compact('brand'));
    }

    public function update(UpdateBrandRequest $request, Brand $brand)
    {
        $data = $request->validated();

        $brand->update($data);

        return redirect()->route('brands.index')->with('success', 'Marca actualizada');
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();

        return redirect()->route('brands.index')->with('success', 'Marca eliminada');
    }
}
