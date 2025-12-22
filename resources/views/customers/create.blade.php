@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Nuevo Cliente</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}" class="text-decoration-none">Clientes</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Nuevo</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">Crear Cliente</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('customers.store') }}" method="post">
                        @csrf
                        @include('customers.form')
                        <div class="d-flex justify-content-end mt-3">
                            <a href="{{ route('customers.index') }}" class="btn btn-light me-2">Cancelar</a>
                            <button class="btn btn-dark">Guardar</button>
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
                    <p class="text-muted">Agrega la identificación correctamente para evitar duplicados.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
