@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Reporte Mensual</h2>
            <small class="text-muted">Periodo: {{ $dateFrom }} — {{ $dateTo }}</small>
        </div>
        <div>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-dark">Volver al Dashboard</a>
        </div>
    </div>

    <!-- Date Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-auto">
                    <label class="form-label">Fecha desde</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control" />
                </div>
                <div class="col-auto">
                    <label class="form-label">Fecha hasta</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control" />
                </div>
                <div class="col-auto align-self-end">
                    <button class="btn btn-dark">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="card p-3">
                        <h6>Total Ventas</h6>
                        <h3>${{ number_format($totalSales, 2) }}</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3">
                        <h6>IVA Recaudado</h6>
                        <h3>${{ number_format($totalIva, 2) }}</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3">
                        <h6>Totales por Forma de Pago</h6>
                        <p>Efectivo: <strong>${{ number_format($byCash, 2) }}</strong></p>
                        <p>Transferencia: <strong>${{ number_format($byTransfer, 2) }}</strong></p>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <p>Facturas emitidas: <strong>{{ $countEmitted }}</strong></p>
                <p>Facturas pendientes: <strong>{{ $countPending }}</strong></p>
            </div>

            <div class="mb-3">
                <a href="{{ route('reports.export', ['format' => 'excel', 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}" class="btn btn-outline-success">
                    <i class="bi bi-file-earmark-excel me-2"></i>Exportar a Excel
                </a>
                <a href="{{ route('reports.export', ['format' => 'pdf', 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}" class="btn btn-outline-danger">
                    <i class="bi bi-file-earmark-pdf me-2"></i>Exportar a PDF
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
