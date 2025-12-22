@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Dashboard de Inventario</h2>
        </div>
        <!-- header actions intentionally removed per request -->
    </div>

    @php
        // Ensure variables are defined in case the view is rendered without a controller context
        $lowStockCount = $lowStockCount ?? 0;
        $totalProducts = $totalProducts ?? 0;
        $totalMovements = $totalMovements ?? 0;
        $recentMovements = $recentMovements ?? collect();
        $stockData = $stockData ?? collect();
        $monthlyMovements = $monthlyMovements ?? 0;
        $monthlySalesTotal = $monthlySalesTotal ?? 0;
        $monthlyInvoices = $monthlyInvoices ?? 0;
        $recentInvoices = $recentInvoices ?? collect();
        $pendingInvoicesCount = $pendingInvoicesCount ?? 0;
        $salesLabels = $salesLabels ?? [];
        $salesData = $salesData ?? [];
        $topProducts = $topProducts ?? collect();
    @endphp

    @if($lowStockCount > 0)
    <div class="alert alert-danger d-flex align-items-center border-0 shadow-sm">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <div>
            <strong>Atención:</strong> Hay {{ $lowStockCount }} productos con stock bajo. Revisa el inventario.
        </div>
    </div>
    @endif

    <!-- Stats Cards Row -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card bg-white shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-exclamation-triangle fs-2 text-danger"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-muted">Stock Bajo</h6>
                            <h3 class="mb-0">{{ $lowStockCount }}</h3>
                            <small class="text-muted">productos</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-sm-6">
            <div class="card bg-white shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-box-seam fs-2 text-dark"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-muted">Total Productos</h6>
                            <h3 class="mb-0">{{ $totalProducts }}</h3>
                            <small class="text-muted">en inventario</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card bg-white shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-arrow-left-right fs-2 text-dark"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-muted">Total Movimientos</h6>
                            <h3 class="mb-0">{{ $totalMovements }}</h3>
                            <small class="text-muted">registrados</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card bg-white shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-graph-up fs-2 text-success"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-muted">Movimientos del Mes</h6>
                            <h3 class="mb-0">{{ $monthlyMovements ?? 0 }}</h3>
                            <small class="text-muted">este mes</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity and Charts Row -->
    <!-- Sales Summary Row -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card bg-white shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-currency-dollar fs-2 text-success"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-muted">Ventas del Mes</h6>
                            <h3 class="mb-0">{{ number_format($monthlySalesTotal ?? 0, 2) }}</h3>
                            <small class="text-muted">en {{ now()->format('F Y') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card bg-white shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-receipt fs-2 text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-muted">Facturas este mes</h6>
                            <h3 class="mb-0">{{ $monthlyInvoices ?? 0 }}</h3>
                            <small class="text-muted">({{ $pendingInvoicesCount ?? 0 }} activas)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Activity -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Últimos Movimientos</h5>
                    <a href="{{ route('movements.index') }}" class="btn btn-sm btn-outline-dark">
                        Ver todos <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0">Producto</th>
                                    <th class="border-0">Tipo</th>
                                    <th class="border-0">Cantidad</th>
                                    <th class="border-0">Usuario</th>
                                    <th class="border-0">Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentMovements as $movement)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="ms-2">
                                                {{ $movement->product->name }}
                                                @if($movement->product->stock < $movement->product->min_stock)
                                                    <span class="badge bg-danger ms-1">Stock Bajo</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                            <td>
                                                <span class="badge {{ $movement->type === 'entrada' ? 'bg-success' : 'bg-danger' }}">
                                                    {{ ucfirst($movement->type) }}
                                                </span>
                                            </td>
                                    <td>{{ $movement->quantity }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-person-circle me-2"></i>
                                            {{ $movement->user->name ?? 'Sistema' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            {{ $movement->created_at->format('d/m/Y H:i') }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                        No hay movimientos recientes.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Quick Access -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Acciones Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <a href="{{ route('products.create') }}" class="btn btn-outline-dark">
                            <i class="bi bi-plus-circle me-2"></i>Nuevo Producto
                        </a>
                        <a href="{{ route('movements.create') }}" class="btn btn-outline-dark">
                            <i class="bi bi-arrow-left-right me-2"></i>Registrar Movimiento
                        </a>
                        <a href="{{ route('suppliers.create') }}" class="btn btn-outline-dark">
                            <i class="bi bi-person-plus me-2"></i>Nuevo Proveedor
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Invoices -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Últimas Ventas</h5>
                    <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-outline-dark">Ver todas</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($recentInvoices as $inv)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold">{{ $inv->invoice_number }}</div>
                                    <small class="text-muted">{{ optional($inv->customer)->first_name }} {{ optional($inv->customer)->last_name }}</small>
                                </div>
                                <div class="text-end">
                                    <div>{{ number_format($inv->total,2) }}</div>
                                    <small class="text-muted">{{ $inv->date->format('d/m/Y') }}</small>
                                </div>
                            </div>
                        @empty
                            <div class="list-group-item text-center text-muted">No hay ventas recientes.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Charts placeholders removed from side column; charts will appear centered below -->
        </div>
    </div>

    <!-- Centered Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-12 d-flex justify-content-center">
            <div class="card shadow-sm w-100" style="max-width:1100px;">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0"><i class="bi bi-graph-up me-2"></i>Analítica</h5>
                </div>
                <div class="card-body">
                    <!-- Sales (full width) -->
                    <div class="mb-4">
                        <h6 class="text-muted">Ventas - Últimos 12 meses</h6>
                        <canvas id="salesChart" class="dashboard-chart-canvas" height="120"></canvas>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted">Stock por Producto</h6>
                            <canvas id="stockChart" class="dashboard-chart-canvas" height="200"></canvas>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted">Top productos vendidos</h6>
                            <canvas id="topProductsChart" class="dashboard-chart-canvas" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    /* Prevent canvases from expanding beyond a reasonable height */
    .dashboard-chart-canvas {
        max-height: 420px !important;
        width: 100% !important;
        display: block !important;
    }
    .card-body { overflow: visible; }
</style>

<script>
    // Función para configurar tema común de los gráficos
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                titleColor: '#fff',
                bodyColor: '#fff',
                padding: 12,
                cornerRadius: 4
            }
        }
    };

    // Gráfico de stock por producto
    const ctx = document.getElementById('stockChart').getContext('2d');
    const stockChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($stockData->pluck('name')) !!},
            datasets: [{
                label: 'Stock actual',
                data: {!! json_encode($stockData->pluck('stock')) !!},
                backgroundColor: 'rgba(33, 37, 41, 0.6)',
                borderColor: 'rgba(33, 37, 41, 1)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            ...commonOptions,
            maintainAspectRatio: true,
            aspectRatio: 1.8,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { 
                        stepSize: 10,
                        color: '#6c757d'
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    ticks: { 
                        color: '#6c757d',
                        maxRotation: 45,
                        minRotation: 45
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
    // Gráfico de ventas (línea) - últimos 12 meses
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($salesLabels) !!},
            datasets: [{
                label: 'Ventas',
                data: {!! json_encode($salesData) !!},
                fill: true,
                backgroundColor: 'rgba(13,110,253,0.08)',
                borderColor: 'rgba(13,110,253,0.9)',
                pointBackgroundColor: 'rgba(13,110,253,1)',
                tension: 0.25
            }]
        },
        options: {
            ...commonOptions,
            maintainAspectRatio: true,
            aspectRatio: 3.2,
            scales: {
                y: { beginAtZero: true, ticks: { color: '#6c757d' }, grid: { color: 'rgba(0,0,0,0.05)' } },
                x: { ticks: { color: '#6c757d' }, grid: { display: false } }
            }
        }
    });

    // Gráfico Top productos (barra horizontal)
    const topCtx = document.getElementById('topProductsChart').getContext('2d');
    const topProducts = {!! json_encode($topProducts->pluck('product_name')) !!};
    const topQuantities = {!! json_encode($topProducts->pluck('total_qty')) !!};
    const topProductsChart = new Chart(topCtx, {
        type: 'bar',
        data: {
            labels: topProducts,
            datasets: [{
                label: 'Cantidad vendida',
                data: topQuantities,
                backgroundColor: 'rgba(33,37,41,0.7)',
                borderColor: 'rgba(33,37,41,1)',
                borderWidth: 1
            }]
        },
        options: {
            ...commonOptions,
            maintainAspectRatio: true,
            aspectRatio: 1.6,
            indexAxis: 'y',
            scales: {
                x: { beginAtZero: true, ticks: { color: '#6c757d' }, grid: { color: 'rgba(0,0,0,0.05)' } },
                y: { ticks: { color: '#6c757d' }, grid: { display: false } }
            }
        }
    });
</script>
@endpush