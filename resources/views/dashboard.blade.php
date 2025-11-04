@extends('layouts.app')

@section('content')
<h2 class="mb-4">Dashboard de Inventario</h2>

{{-- Alerta visual si hay productos críticos --}}
@if($lowStockCount > 0)
<div class="alert alert-danger d-flex align-items-center gap-2">
    <strong>⚠️ Atención:</strong> Hay {{ $lowStockCount }} productos con stock bajo. Revisa el inventario.
</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-danger text-bg-dark h-100">
            <div class="card-body">
                <h5 class="card-title text-danger">Stock bajo</h5>
                <p class="card-text fs-4">{{ $lowStockCount }} productos</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-primary text-bg-dark h-100">
            <div class="card-body">
                <h5 class="card-title text-primary">Total productos</h5>
                <p class="card-text fs-4">{{ $totalProducts }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-warning text-bg-dark h-100">
            <div class="card-body">
                <h5 class="card-title text-warning">Total movimientos</h5>
                <p class="card-text fs-4">{{ $totalMovements }}</p>
            </div>
        </div>
    </div>
</div>

<h4 class="mb-3">Últimos movimientos</h4>
<div class="table-responsive">
    <table class="table table-sm table-bordered align-middle">
        <thead class="table-dark">
            <tr>
                <th>Producto</th>
                <th>Tipo</th>
                <th>Cantidad</th>
                <th>Usuario</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentMovements as $movement)
            <tr class="{{ $movement->product->stock < $movement->product->min_stock ? 'table-danger' : '' }}">
                <td>{{ $movement->product->name }}</td>
                <td>{{ ucfirst($movement->type) }}</td>
                <td>{{ $movement->quantity }}</td>
                <td>{{ $movement->user->name ?? 'Sistema' }}</td>
                <td>{{ $movement->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center text-muted">No hay movimientos recientes.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="row g-4 mt-4">
    <div class="col-md-6">
        <div class="card border-light shadow-sm">
            <div class="card-header bg-dark text-white">
                📊 Stock por producto
            </div>
            <div class="card-body bg-light">
                <canvas id="stockChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-light shadow-sm">
            <div class="card-header bg-dark text-white">
                🔁 Movimientos por tipo
            </div>
            <div class="card-body bg-light">
                <canvas id="movementChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Gráfico de stock por producto
    const ctx = document.getElementById('stockChart').getContext('2d');
    const stockChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($stockData->pluck('name')) !!},
            datasets: [{
                label: 'Stock actual',
                data: {!! json_encode($stockData->pluck('stock')) !!},
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 10 }
                }
            }
        }
    });

    // Gráfico de movimientos por tipo
    const movementCtx = document.getElementById('movementChart').getContext('2d');
    const movementChart = new Chart(movementCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($movementStats->pluck('type')->map(fn($t) => ucfirst($t))) !!},
            datasets: [{
                label: 'Cantidad de movimientos',
                data: {!! json_encode($movementStats->pluck('total')) !!},
                backgroundColor: 'rgba(255, 99, 132, 0.6)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
</script>
@endpush