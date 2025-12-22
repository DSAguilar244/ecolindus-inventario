@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Ventas por Cliente</h1>

    <form class="row g-2 mb-3" method="get" action="{{ route('reports.sales_by_customer') }}">
        <div class="col-md-3">
            <label>Fecha desde</label>
            <input type="date" name="date_from" class="form-control" value="{{ $date_from ?? '' }}">
        </div>
        <div class="col-md-3">
            <label>Fecha hasta</label>
            <input type="date" name="date_to" class="form-control" value="{{ $date_to ?? '' }}">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button class="btn btn-primary me-2">Filtrar</button>
            <a class="btn btn-outline-secondary" target="_blank" href="{{ route('reports.sales_by_customer', array_merge(request()->query(), ['export' => 'pdf'])) }}">Exportar PDF</a>
        </div>
    </form>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Facturas</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
        @forelse($results as $row)
            <tr>
                <td>{{ $row['customer_name'] }}</td>
                <td>{{ $row['invoices_count'] }}</td>
                <td>{{ number_format($row['total'], 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="3">No hay datos</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
