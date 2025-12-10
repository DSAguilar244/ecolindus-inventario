@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Historial de Cajas</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Historial de Cajas</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-dark">Volver al Dashboard</a>
        </div>
    </div>

    @if ($sessions->count() > 0)
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Usuario</th>
                                <th>Apertura</th>
                                <th>Cierre</th>
                                <th>Estado</th>
                                <th class="text-end">Facturas</th>
                                <th class="text-end">Efectivo</th>
                                <th class="text-end">Transferencia</th>
                                <th class="text-end">Total Facturado</th>
                                <th class="text-end">Total Caja</th>
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
                                @endphp
                                <tr>
                                    <td>
                                        <small class="text-muted">
                                            {{ $session->user?->name ?? 'Usuario desconocido' }}
                                        </small>
                                    </td>
                                    <td>
                                        <small>
                                            {{ $session->opened_at?->format('Y-m-d H:i') ?? '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        <small>
                                            {{ $session->closed_at?->format('Y-m-d H:i') ?? '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        @if ($session->status === 'open')
                                            <span class="badge bg-success">Abierta</span>
                                        @else
                                            <span class="badge bg-secondary">Cerrada</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <strong>{{ $invoiceCount }}</strong>
                                    </td>
                                    <td class="text-end text-success">
                                        <strong>${{ number_format($totalCash, 2) }}</strong>
                                    </td>
                                    <td class="text-end text-info">
                                        <strong>${{ number_format($totalTransfer, 2) }}</strong>
                                    </td>
                                    <td class="text-end">
                                        <strong>${{ number_format($totalInvoiced, 2) }}</strong>
                                    </td>
                                    <td class="text-end">
                                        <strong>${{ number_format($totalCash + $totalTransfer, 2) }}</strong>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            @php
                                $allInvoices = \App\Models\Invoice::whereIn(
                                    'user_id',
                                    $sessions->pluck('user_id')->unique()->toArray()
                                )
                                    ->where('status', 'emitida')
                                    ->whereBetween('date', [
                                        $sessions->min('opened_at'),
                                        $sessions->max('closed_at') ?? now(),
                                    ])
                                    ->with('payment')
                                    ->get();

                                $sumCash = $allInvoices->sum(fn($inv) => $inv->payment?->cash_amount ?? 0);
                                $sumTransfer = $allInvoices->sum(fn($inv) => $inv->payment?->transfer_amount ?? 0);
                                $sumTotal = $allInvoices->sum('total');
                            @endphp
                            <tr>
                                <td colspan="4" class="text-end"><strong>TOTALES:</strong></td>
                                <td class="text-end"><strong>{{ $allInvoices->count() }}</strong></td>
                                <td class="text-end"><strong>${{ number_format($sumCash, 2) }}</strong></td>
                                <td class="text-end"><strong>${{ number_format($sumTransfer, 2) }}</strong></td>
                                <td class="text-end"><strong>${{ number_format($sumTotal, 2) }}</strong></td>
                                <td class="text-end"><strong>${{ number_format($sumCash + $sumTransfer, 2) }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($sessions->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $sessions->links() }}
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="alert alert-info d-flex align-items-center">
            <i class="bi bi-info-circle me-2"></i>
            <div>No hay sesiones de caja registradas.</div>
        </div>
    @endif
</div>
@endsection
