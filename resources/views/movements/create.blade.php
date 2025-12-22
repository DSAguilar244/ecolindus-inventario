@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Registrar Movimiento</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('movements.index') }}" class="text-decoration-none">Movimientos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Nuevo</li>
                </ol>
            </nav>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0"><i class="bi bi-plus-circle me-2"></i>Nuevo Movimiento</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('movements.store') }}" method="POST" id="createMovementForm">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="product_id" class="form-label">Producto</label>
                                <select name="product_id" id="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
                                    <option value="">-- Selecciona --</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}" {{ (old('product_id') == $p->id || (isset($product) && $product->id == $p->id)) ? 'selected' : '' }}>
                                            {{ $p->name }} (Stock: {{ $p->stock }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="type" class="form-label">Tipo</label>
                                <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="">-- Selecciona --</option>
                                    <option value="entrada" {{ old('type') == 'entrada' || (isset($type) && $type == 'entrada') ? 'selected' : '' }}>Entrada</option>
                                    <option value="salida" {{ old('type') == 'salida' || (isset($type) && $type == 'salida') ? 'selected' : '' }}>Salida</option>
                                    <option value="dañado" {{ old('type') == 'dañado' ? 'selected' : '' }}>Dañado</option>
                                    <option value="devuelto" {{ old('type') == 'devuelto' ? 'selected' : '' }}>Devuelto</option>
                                </select>
                                @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="quantity" class="form-label">Cantidad</label>
                                <input type="number" name="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity', 1) }}" min="1" required>
                                @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-8">
                                <label for="supplier_id" class="form-label">Proveedor (opcional)</label>
                                <select name="supplier_id" id="supplier_id" class="form-select">
                                    <option value="">-- Ninguno --</option>
                                    @foreach($suppliers as $s)
                                        <option value="{{ $s->id }}" {{ old('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label for="reason" class="form-label">Motivo</label>
                                <textarea name="reason" id="reason" class="form-control" rows="2">{{ old('reason') }}</textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-white border-0 pt-0">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('movements.index') }}" class="btn btn-light">Cancelar</a>
                        <button type="submit" form="createMovementForm" class="btn btn-dark">Registrar Movimiento</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3"><h5 class="mb-0">Acciones Rápidas</h5></div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('products.index') }}" class="btn btn-outline-dark">Gestionar Productos</a>
                        <a href="{{ route('suppliers.index') }}" class="btn btn-outline-dark">Gestionar Proveedores</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection