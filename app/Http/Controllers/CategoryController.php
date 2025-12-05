<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $items = Category::orderBy('name')->paginate(20);

        return view('categories.index', ['categories' => $items]);
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(StoreCategoryRequest $request)
    {
        $data = $request->validated();

        // Avoid duplicates - if a category with the same name already exists, return 409
        $exists = Category::where('name', $data['name'])->first();
        if ($exists) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Categoría ya existe', 'category' => $exists], 409);
            }

            return redirect()->route('categories.index')->with('error', 'Categoría con ese nombre ya existe');
        }

        $category = Category::create($data);
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['id' => $category->id, 'text' => $category->name, 'category' => $category], 201);
        }

        return redirect()->route('categories.index')->with('success', 'Categoría creada');
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $data = $request->validated();

        $category->update($data);

        return redirect()->route('categories.index')->with('success', 'Categoría actualizada');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Categoría eliminada');
    }
}
