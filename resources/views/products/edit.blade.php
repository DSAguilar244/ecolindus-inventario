@extends('layouts.app')

@section('content')
<h2 class="mb-4">✏️ Editar producto</h2>

<form action="{{ route('products.update', $product) }}" method="POST" class="bg-dark text-white p-4 rounded shadow-sm">
    @csrf @method('PUT')

    <div class="row g-3">
        <div class="col-md-6">
            <label for="name" class="form-label">Nombre del producto</label>
            <input type="text" name="name" id="name" class="form-control bg-light" value="{{ $product->name }}" required>
        </div>

        <div class="col-md-6">
            <label for="category" class="form-label">Categoría</label>
            <input type="text" name="category" id="category" class="form-control bg-light" value="{{ $product->category }}" required>
        </div>

        <div class="col-md-6">
            <label for="unit" class="form-label">Unidad de medida</label>
            <input type="text" name="unit" id="unit" class="form-control bg-light" value="{{ $product->unit }}" required>
        </div>

        <div class="col-md-3">
            <label for="stock" class="form-label">Stock actual</label>
            <input type="number" name="stock" id="stock" class="form-control bg-light" value="{{ $product->stock }}" min="0">
        </div>

        <div class="col-md-3">
            <label for="min_stock" class="form-label">Stock mínimo</label>
            <input type="number" name="min_stock" id="min_stock" class="form-control bg-light" value="{{ $product->min_stock }}" min="0">
        </div>
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary px-4">💾 Actualizar</button>
        <a href="{{ route('products.index') }}" class="btn btn-outline-light px-4">↩️ Cancelar</a>
    </div>
</form>
@endsection