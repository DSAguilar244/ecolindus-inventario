@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Editar Cliente</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}" class="text-decoration-none">Clientes</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Editar</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('customers.index') }}" class="btn btn-outline-dark">Volver a clientes</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">Editar Cliente</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('customers.update', $customer) }}" method="post">
                        @csrf @method('PUT')
                        @include('customers.form')
                        <div class="d-flex justify-content-end mt-3">
                            <a class="btn btn-light me-2" href="{{ route('customers.index') }}">Cancelar</a>
                            <button class="btn btn-dark" type="submit">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">Información</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Si cambias la identificación, verifica que no exista otro cliente con la misma identificación.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
