@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <h2>Lista de Clientes</h2>
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Identificación</th>
                <th>Nombre</th>
                <th>Teléfono</th>
                <th>Email</th>
                <th>Dirección</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $c)
            <tr>
                <td>{{ $c->identification }}</td>
                <td>{{ $c->first_name }} {{ $c->last_name }}</td>
                <td>{{ $c->phone }}</td>
                <td>{{ $c->email }}</td>
                <td>{{ Str::limit($c->address, 80) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
