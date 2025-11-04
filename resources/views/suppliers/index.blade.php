@extends('layouts.app')

@section('content')
<h2 class="mb-4">📦 Proveedores</h2>

<div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
    <a href="{{ route('suppliers.create') }}" class="btn btn-primary">➕ Nuevo proveedor</a>
    <a href="{{ route('suppliers.export.pdf') }}" class="btn btn-outline-dark">🗎 Exportar PDF</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="table-responsive">
    <table class="table table-bordered table-sm align-middle mb-0">
        <thead class="table-dark text-white">
            <tr>
                <th>Nombre</th>
                <th>Contacto</th>
                <th>Email</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($suppliers as $supplier)
            <tr>
                <td>{{ $supplier->name }}</td>
                <td>{{ $supplier->contact }}</td>
                <td>{{ $supplier->email }}</td>
                <td class="text-nowrap">
                    <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-sm btn-outline-warning">✏️ Editar</a>
                    <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar proveedor?')">🗑️ Eliminar</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center text-muted">No hay proveedores registrados.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($suppliers->hasPages())
    <nav class="d-flex justify-content-center mt-4">
        <ul class="pagination pagination-sm">
            {{-- Anterior --}}
            @if ($suppliers->onFirstPage())
                <li class="page-item disabled"><span class="page-link">Anterior</span></li>
            @else
                <li class="page-item"><a class="page-link" href="{{ $suppliers->previousPageUrl() }}">Anterior</a></li>
            @endif

            {{-- Números --}}
            @foreach ($suppliers->links()->elements[0] as $page => $url)
                @if ($page == $suppliers->currentPage())
                    <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                @endif
            @endforeach

            {{-- Siguiente --}}
            @if ($suppliers->hasMorePages())
                <li class="page-item"><a class="page-link" href="{{ $suppliers->nextPageUrl() }}">Siguiente</a></li>
            @else
                <li class="page-item disabled"><span class="page-link">Siguiente</span></li>
            @endif
        </ul>
    </nav>
@endif
@endsection