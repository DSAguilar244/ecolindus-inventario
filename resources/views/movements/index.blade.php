@extends('layouts.app')

@section('content')
<h2 class="mb-4">🔁 Movimientos de Inventario</h2>

<div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
    <a href="{{ route('movements.create') }}" class="btn btn-primary">➕ Registrar movimiento</a>
    <a href="{{ route('movements.export.pdf') }}" class="btn btn-outline-dark">🗎 Exportar PDF</a>
</div>

{{-- Filtros por fecha y tipo --}}
<form method="GET" action="{{ route('movements.index') }}" class="row g-2 mb-4">
    <div class="col-md-3">
        <input type="date" name="from" class="form-control" value="{{ request('from') }}">
    </div>
    <div class="col-md-3">
        <input type="date" name="to" class="form-control" value="{{ request('to') }}">
    </div>
    <div class="col-md-3">
        <select name="type" class="form-select">
            <option value="">Todos</option>
            <option value="entrada" {{ request('type') == 'entrada' ? 'selected' : '' }}>Entrada</option>
            <option value="salida" {{ request('type') == 'salida' ? 'selected' : '' }}>Salida</option>
        </select>
    </div>
    <div class="col-md-3">
        <button type="submit" class="btn btn-outline-primary w-100">🔎 Filtrar</button>
    </div>
</form>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="table-responsive">
    <table class="table table-bordered table-sm align-middle mb-0">
        <thead class="table-dark text-white">
            <tr>
                <th>Producto</th>
                <th>Tipo</th>
                <th>Cantidad</th>
                <th>Proveedor</th>
                <th>Motivo</th>
                <th>Usuario</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movements as $movement)
            <tr class="{{ $movement->product->stock < $movement->product->min_stock ? 'table-danger' : '' }}">
                <td>{{ $movement->product->name }}</td>
                <td>{{ ucfirst($movement->type) }}</td>
                <td>{{ $movement->quantity }}</td>
                <td>{{ $movement->supplier->name ?? '-' }}</td>
                <td>{{ $movement->reason }}</td>
                <td>{{ $movement->user->name ?? 'Sistema' }}</td>
                <td>{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                <td class="text-nowrap">
                    <form action="{{ route('movements.destroy', $movement) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar movimiento?')">🗑️ Eliminar</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center text-muted">No hay movimientos registrados.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($movements->hasPages())
    <nav class="d-flex justify-content-center mt-4">
        <ul class="pagination pagination-sm">
            {{-- Anterior --}}
            @if ($movements->onFirstPage())
                <li class="page-item disabled"><span class="page-link">Anterior</span></li>
            @else
                <li class="page-item"><a class="page-link" href="{{ $movements->previousPageUrl() }}">Anterior</a></li>
            @endif

            {{-- Números --}}
            @foreach ($movements->links()->elements[0] as $page => $url)
                @if ($page == $movements->currentPage())
                    <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                @endif
            @endforeach

            {{-- Siguiente --}}
            @if ($movements->hasMorePages())
                <li class="page-item"><a class="page-link" href="{{ $movements->nextPageUrl() }}">Siguiente</a></li>
            @else
                <li class="page-item disabled"><span class="page-link">Siguiente</span></li>
            @endif
        </ul>
    </nav>
@endif
@endsection