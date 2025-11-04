@extends('layouts.app')

@section('content')
<h2 class="mb-4">➕ Registrar movimiento</h2>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('movements.store') }}" method="POST" class="bg-dark text-white p-4 rounded shadow-sm">
    @csrf

    <div class="row g-3">
        <div class="col-md-6">
            <label for="product_id" class="form-label">Producto</label>
            <select name="product_id" id="product_id" class="form-select bg-light" required>
                <option value="">-- Selecciona --</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }} (Stock: {{ $product->stock }})</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6">
            <label for="type" class="form-label">Tipo de movimiento</label>
            <select name="type" id="type" class="form-select bg-light" required>
                <option value="">-- Selecciona --</option>
                <option value="entrada">Entrada</option>
                <option value="salida">Salida</option>
                <option value="dañado">Dañado</option>
                <option value="devuelto">Devuelto</option>
            </select>
        </div>

        <div class="col-md-4">
            <label for="quantity" class="form-label">Cantidad</label>
            <input type="number" name="quantity" id="quantity" class="form-control bg-light" min="1" required>
        </div>

        <div class="col-md-8">
            <label for="supplier_id" class="form-label">Proveedor (opcional)</label>
            <select name="supplier_id" id="supplier_id" class="form-select bg-light">
                <option value="">-- Ninguno --</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-12">
            <label for="reason" class="form-label">Motivo</label>
            <textarea name="reason" id="reason" class="form-control bg-light" rows="2"></textarea>
        </div>
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-success px-4">💾 Registrar</button>
        <a href="{{ route('movements.index') }}" class="btn btn-outline-light px-4">↩️ Cancelar</a>
    </div>
</form>
@endsection