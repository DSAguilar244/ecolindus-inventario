@extends('layouts.app')

@section('content')
<div class="container-fluid py-3 py-lg-4">
    <!-- Header responsive -->
    <div class="row mb-3 mb-lg-4 align-items-start">
        <div class="col">
            <h2 class="mb-2">Historial de Cajas</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Historial</li>
                </ol>
            </nav>
        </div>
        <div class="col-auto">
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-dark mb-2 d-lg-none w-100" style="min-height: 40px;">
                ← Volver
            </a>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-dark d-none d-lg-inline-block">
                ← Dashboard
            </a>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-3 shadow-sm">
        <div class="card-body">
            <h6 class="card-title mb-3">Filtros</h6>
            <form method="GET" class="row g-2 g-lg-3">
                <div class="col-12 col-sm-6 col-lg-auto">
                    <label for="filterUser" class="form-label small">Usuario</label>
                    <select name="user_id" id="filterUser" class="form-select form-select-sm" style="min-height: 38px;">
                        <option value="">Todos</option>
                        @foreach(\App\Models\User::orderBy('name')->get() as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-auto">
                    <label for="filterFrom" class="form-label small">Desde</label>
                    <input type="date" id="filterFrom" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm" style="min-height: 38px;" />
                </div>
                <div class="col-12 col-sm-6 col-lg-auto">
                    <label for="filterTo" class="form-label small">Hasta</label>
                    <input type="date" id="filterTo" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm" style="min-height: 38px;" />
                </div>
                <div class="col-12 col-sm-6 col-lg-auto d-flex align-items-end">
                    <button type="submit" class="btn btn-dark w-100 w-lg-auto" style="min-height: 38px;">
                        <i class="bi bi-funnel me-1"></i>Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if ($sessions->count() > 0)
        <!-- Desktop: Table View -->
        <div class="d-none d-lg-block card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Usuario</th>
                                <th scope="col">Apertura</th>
                                <th scope="col">Estado</th>
                                <th scope="col" class="text-end">Facturas</th>
                                <th scope="col" class="text-end">Efectivo</th>
                                <th scope="col" class="text-end">Transferencia</th>
                                <th scope="col" class="text-end">Total Fact.</th>
                                <th scope="col" class="text-end">Diferencia</th>
                                <th scope="col" style="max-width: 250px;">Notas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sessions as $session)
                                @php
                                    $invoices = \App\Models\Invoice::where('user_id', $session->user_id)
                                        ->where('status', 'emitida')
                                        ->whereBetween('date', [$session->opened_at, $session->closed_at ?? now()])
                                        ->with('payment')
                                        ->get();
                                    $totalCash = $invoices->sum(fn($inv) => $inv->payment?->cash_amount ?? 0);
                                    $totalTransfer = $invoices->sum(fn($inv) => $inv->payment?->transfer_amount ?? 0);
                                    $totalInvoiced = $invoices->sum('total');
                                    $invoiceCount = $invoices->count();
                                    
                                    // Calculate difference: Expected (opening + cash + transfer) - Reported (found in cash)
                                    $expected = ($session->opening_amount ?? 0) + $totalCash + $totalTransfer;
                                    $difference = $session->status === 'closed' ? ($session->difference ?? 0) : ($expected - ($session->reported_closing_amount ?? $expected));
                                @endphp
                                <tr>
                                    <td><small>{{ $session->user?->name ?? 'N/A' }}</small></td>
                                    <td><small>{{ $session->opened_at?->format('d/m/y H:i') ?? '-' }}</small></td>
                                    <td>
                                        @if ($session->status === 'open')
                                            <span class="badge bg-success">Abierta</span>
                                        @else
                                            <span class="badge bg-secondary">Cerrada</span>
                                        @endif
                                    </td>
                                    <td class="text-end"><strong>{{ $invoiceCount }}</strong></td>
                                    <td class="text-end"><small class="text-success">${{ number_format($totalCash, 2) }}</small></td>
                                    <td class="text-end"><small class="text-info">${{ number_format($totalTransfer, 2) }}</small></td>
                                    <td class="text-end"><strong>${{ number_format($totalInvoiced, 2) }}</strong></td>
                                    <td class="text-end" data-bs-toggle="tooltip" data-bs-html="true" title="<strong>Arqueo de Caja</strong><br/>Monto Inicial: ${{ number_format($session->opening_amount ?? 0, 2) }}<br/>+ Efectivo: ${{ number_format($totalCash, 2) }}<br/>+ Transferencia: ${{ number_format($totalTransfer, 2) }}<br/><strong>= Monto Esperado: ${{ number_format($expected, 2) }}</strong><br/><br/>Monto Encontrado: ${{ number_format($session->reported_closing_amount ?? $expected, 2) }}<br/><strong>Diferencia: ${{ number_format($difference, 2) }}</strong>">
                                        <small class="{{ $difference > 0 ? 'text-warning' : ($difference < 0 ? 'text-danger' : 'text-success') }}">
                                            ${{ number_format($difference, 2) }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($session->notes)
                                            <small class="text-muted" title="{{ $session->notes }}" data-bs-toggle="tooltip">
                                                {{ substr($session->notes, 0, 50) }}{{ strlen($session->notes) > 50 ? '...' : '' }}
                                            </small>
                                        @else
                                            <small class="text-muted">-</small>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($sessions->hasPages())
                    <nav class="mt-3" aria-label="Paginación">
                        {{ $sessions->links() }}
                    </nav>
                @endif
            </div>
        </div>

        <!-- Mobile: Cards View -->
        <div class="d-lg-none">
            @foreach ($sessions as $session)
                @php
                    $invoices = \App\Models\Invoice::where('user_id', $session->user_id)
                        ->where('status', 'emitida')
                        ->whereBetween('date', [$session->opened_at, $session->closed_at ?? now()])
                        ->with('payment')
                        ->get();
                    $totalCash = $invoices->sum(fn($inv) => $inv->payment?->cash_amount ?? 0);
                    $totalTransfer = $invoices->sum(fn($inv) => $inv->payment?->transfer_amount ?? 0);
                    $totalInvoiced = $invoices->sum('total');
                    $invoiceCount = $invoices->count();
                    
                    // Calculate difference: Expected (opening + cash + transfer) - Reported (found in cash)
                    $expected = ($session->opening_amount ?? 0) + $totalCash + $totalTransfer;
                    $difference = $session->status === 'closed' ? ($session->difference ?? 0) : ($expected - ($session->reported_closing_amount ?? $expected));
                @endphp
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="mb-0">{{ $session->user?->name ?? 'Usuario' }}</h6>
                            @if ($session->status === 'open')
                                <span class="badge bg-success">Abierta</span>
                            @else
                                <span class="badge bg-secondary">Cerrada</span>
                            @endif
                        </div>
                        <small class="text-muted d-block mb-3">{{ $session->opened_at?->format('d/m/Y H:i') ?? '-' }}</small>

                        <div class="row row-cols-2 g-2 mb-3">
                            <div class="col">
                                <div class="p-2 bg-light rounded">
                                    <small class="d-block text-muted">Facturas</small>
                                    <strong>{{ $invoiceCount }}</strong>
                                </div>
                            </div>
                            <div class="col">
                                <div class="p-2 bg-light rounded">
                                    <small class="d-block text-muted">Total</small>
                                    <strong class="text-success">${{ number_format($totalInvoiced, 2) }}</strong>
                                </div>
                            </div>
                            <div class="col">
                                <div class="p-2 bg-light rounded">
                                    <small class="d-block text-muted">Efectivo</small>
                                    <strong class="text-primary">${{ number_format($totalCash, 2) }}</strong>
                                </div>
                            </div>
                            <div class="col">
                                <div class="p-2 bg-light rounded" data-bs-toggle="tooltip" data-bs-html="true" title="<strong>Arqueo de Caja</strong><br/>Monto Inicial: ${{ number_format($session->opening_amount ?? 0, 2) }}<br/>+ Efectivo: ${{ number_format($totalCash, 2) }}<br/>+ Transferencia: ${{ number_format($totalTransfer, 2) }}<br/><strong>= Monto Esperado: ${{ number_format($expected, 2) }}</strong><br/><br/>Monto Encontrado: ${{ number_format($session->reported_closing_amount ?? $expected, 2) }}<br/><strong>Diferencia: ${{ number_format($difference, 2) }}</strong>">
                                    <small class="d-block text-muted">Diferencia</small>
                                    <strong class="{{ $difference > 0 ? 'text-warning' : ($difference < 0 ? 'text-danger' : 'text-success') }}">
                                        ${{ number_format($difference, 2) }}
                                    </strong>
                                </div>
                            </div>
                        </div>

                        @if($session->notes)
                            <div class="alert alert-info p-2 mb-0">
                                <small><strong>Nota:</strong> {{ $session->notes }}</small>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            @if ($sessions->hasPages())
                <nav class="mt-3" aria-label="Paginación móvil">
                    {{ $sessions->links() }}
                </nav>
            @endif
        </div>

    @else
        <div class="alert alert-info d-flex align-items-center">
            <i class="bi bi-info-circle me-2"></i>
            <div>No hay sesiones de caja registradas.</div>
        </div>
    @endif

    <!-- Clear History Button -->
    <div class="mt-4 d-flex justify-content-between">
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">Volver</a>
        <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#clearHistoryModal" style="min-height: 44px;">
            <i class="bi bi-trash me-2"></i>Limpiar Historial
        </button>
    </div>
</div>

<!-- Clear History Modal -->
<div class="modal fade" id="clearHistoryModal" tabindex="-1" role="dialog" aria-labelledby="clearHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="clearHistoryModalLabel">Limpiar Historial</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" role="alert">
                    <strong>Advertencia:</strong> Esta acción eliminará TODAS las sesiones cerradas. No se puede deshacer.
                </div>
                <p class="text-muted mb-3">Para confirmar, escribe la palabra <strong>ELIMINAR</strong> en el campo:</p>
                <input type="text" id="confirmText" class="form-control" placeholder="Escribe ELIMINAR" aria-label="Confirmación de eliminación" style="min-height: 44px;" />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button id="confirmClearBtn" type="button" class="btn btn-danger" disabled style="min-height: 44px;">
                    Eliminar Sesiones
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const confirmInput = document.getElementById('confirmText');
    const confirmBtn = document.getElementById('confirmClearBtn');
    const modalEl = document.getElementById('clearHistoryModal');

    // Enable/disable button based on confirmation text
    confirmInput?.addEventListener('input', function (e) {
        confirmBtn.disabled = e.target.value.trim() !== 'ELIMINAR';
    });

    // Clear history button click
    confirmBtn?.addEventListener('click', async () => {
        confirmBtn.disabled = true;
        confirmBtn.textContent = 'Eliminando...';

        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const response = await fetch('{{ route('cash_sessions.clearHistory') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Error en la solicitud');

            const data = await response.json();
            confirmInput.value = '';
            confirmBtn.disabled = true;
            confirmBtn.textContent = 'Eliminar Sesiones';

            // Show non-blocking bootstrap alert instead of browser alert
            bootstrap.Modal.getInstance(modalEl).hide();
            const flashContainerId = 'flashMessageContainer';
            let flashContainer = document.getElementById(flashContainerId);
            if (!flashContainer) {
                flashContainer = document.createElement('div');
                flashContainer.id = flashContainerId;
                flashContainer.style.position = 'fixed';
                flashContainer.style.top = '20px';
                flashContainer.style.right = '20px';
                flashContainer.style.zIndex = 1080;
                document.body.appendChild(flashContainer);
            }

            const msg = `Se eliminaron ${data.deleted ?? 0} sesiones cerradas.`;
            const alertEl = document.createElement('div');
            alertEl.className = 'alert alert-success shadow-sm';
            alertEl.role = 'alert';
            alertEl.innerHTML = msg;
            flashContainer.appendChild(alertEl);

            // Auto-remove after 2 seconds and then reload to refresh data
            setTimeout(() => {
                alertEl.classList.add('fade');
                alertEl.classList.remove('show');
                try { flashContainer.removeChild(alertEl); } catch(e) {}
                location.reload();
            }, 1500);
        } catch (error) {
            // Non-blocking error message
            const flashContainerId = 'flashMessageContainer';
            let flashContainer = document.getElementById(flashContainerId);
            if (!flashContainer) {
                flashContainer = document.createElement('div');
                flashContainer.id = flashContainerId;
                flashContainer.style.position = 'fixed';
                flashContainer.style.top = '20px';
                flashContainer.style.right = '20px';
                flashContainer.style.zIndex = 1080;
                document.body.appendChild(flashContainer);
            }
            const alertEl = document.createElement('div');
            alertEl.className = 'alert alert-danger shadow-sm';
            alertEl.role = 'alert';
            alertEl.innerHTML = 'Error al eliminar historial';
            flashContainer.appendChild(alertEl);
            setTimeout(() => { try { flashContainer.removeChild(alertEl); } catch(e) {} }, 3000);
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Eliminar Sesiones';
        }
    });
});
</script>
@endsection
