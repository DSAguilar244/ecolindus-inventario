@extends('layouts.app')

@section('content')
<h2 class="mb-4">📦 Productos</h2>

<div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
    <a href="{{ route('products.create') }}" class="btn btn-primary">➕ Nuevo producto</a>
    <a href="{{ route('products.export.pdf') }}" class="btn btn-outline-dark">🗎 Exportar PDF</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="table-responsive">
    <table class="table table-bordered table-sm align-middle mb-0">
        <thead class="table-dark text-white">
            <tr>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Unidad</th>
                <th>Stock</th>
                <th>Stock mínimo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
            <tr class="{{ $product->stock < $product->min_stock ? 'table-danger' : '' }}">
                <td>{{ $product->name }}</td>
                <td>{{ $product->category }}</td>
                <td>{{ $product->unit }}</td>
                <td>
                    <span class="fw-semibold {{ $product->stock < $product->min_stock ? 'text-danger' : '' }}">
                        {{ $product->stock }}
                    </span>
                </td>
                <td>{{ $product->min_stock }}</td>
                <td class="text-nowrap">
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-warning">✏️ Editar</a>
                    <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar producto?')">🗑️ Eliminar</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center text-muted">No hay productos registrados.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($products->hasPages())
    <nav class="d-flex justify-content-center mt-4">
        <ul class="pagination pagination-sm">
            {{-- Anterior --}}
            @if ($products->onFirstPage())
                <li class="page-item disabled"><span class="page-link">Anterior</span></li>
            @else
                <li class="page-item"><a class="page-link" href="{{ $products->previousPageUrl() }}">Anterior</a></li>
            @endif

            {{-- Números --}}
            @foreach ($products->links()->elements[0] as $page => $url)
                @if ($page == $products->currentPage())
                    <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                @endif
            @endforeach

            {{-- Siguiente --}}
            @if ($products->hasMorePages())
                <li class="page-item"><a class="page-link" href="{{ $products->nextPageUrl() }}">Siguiente</a></li>
            @else
                <li class="page-item disabled"><span class="page-link">Siguiente</span></li>
            @endif
        </ul>
    </nav>
@endif
@endsection