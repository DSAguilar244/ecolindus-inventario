@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Movimientos de Inventario</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Movimientos</li>
                </ol>
            </nav>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('movements.create') }}" class="btn btn-dark">
                <i class="bi bi-plus-circle me-2"></i>Registrar Movimiento
            </a>
            <a href="{{ route('movements.export.pdf', request()->all()) }}" class="btn btn-outline-dark">
                <i class="bi bi-file-pdf me-2"></i>Exportar PDF
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center border-0 shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Movimientos</h5>
                    <form method="GET" action="{{ route('movements.index') }}" class="d-flex gap-2 align-items-center">
                        <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
                        <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
                        <select name="type" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            <option value="entrada" {{ request('type') == 'entrada' ? 'selected' : '' }}>Entrada</option>
                            <option value="salida" {{ request('type') == 'salida' ? 'selected' : '' }}>Salida</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-outline-primary">Filtrar</button>
                    </form>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 ps-4">Producto</th>
                                    <th class="border-0">Tipo</th>
                                    <th class="border-0">Cantidad</th>
                                    <th class="border-0">Proveedor</th>
                                    <th class="border-0">Motivo</th>
                                    <th class="border-0">Usuario</th>
                                    <th class="border-0">Fecha</th>
                                    <th class="border-0 text-end pe-4">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($movements as $movement)
                                <tr>
                                    <td class="ps-4">
                                        <div>
                                            <strong>{{ $movement->product->name }}</strong>
                                            <div class="text-muted small">Stock: {{ $movement->product->stock }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $movement->type === 'entrada' ? 'bg-success' : 'bg-danger' }}">{{ ucfirst($movement->type) }}</span>
                                    </td>
                                    <td>{{ $movement->quantity }}</td>
                                    <td>{{ $movement->supplier->name ?? '-' }}</td>
                                    <td class="text-muted small">{{ $movement->reason ?? '-' }}</td>
                                    <td>{{ $movement->user->name ?? 'Sistema' }}</td>
                                    <td>{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-end pe-4">
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-movement-id="{{ $movement->id }}">Eliminar</button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="bi bi-inbox display-1 text-muted mb-3 d-block"></i>
                                        <p class="text-muted mb-0">No hay movimientos registrados.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Resumen</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="text-muted">Movimientos totales</div>
                        <div class="h4">{{ $movements->total() }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted">Últimos movimientos</div>
                        <ul class="list-unstyled small mb-0">
                            @foreach($movements->take(5) as $m)
                                <li>{{ $m->product->name }} — {{ ucfirst($m->type) }} ({{ $m->quantity }})</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Eliminar Movimiento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="bi bi-exclamation-triangle text-danger display-3 mb-4 d-block"></i>
                    <h5 class="mb-3">¿Eliminar este movimiento?</h5>
                    <p class="text-muted mb-0">Esta acción no podrá deshacerse.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <form id="deleteForm" action="" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteModal = document.getElementById('deleteModal');
            const baseUrl = '{{ url('movements') }}';
            deleteModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const movementId = button.getAttribute('data-movement-id');
                const form = this.querySelector('#deleteForm');
                // Construir URL usando helper url() para soportar subdirectorios
                form.action = baseUrl + '/' + movementId;
            });
        });
    </script>
    @endpush
</div>
@endsection