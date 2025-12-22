@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Nuevo Proveedor</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('suppliers.index') }}" class="text-decoration-none">Proveedores</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Nuevo</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0"><i class="bi bi-person-plus me-2"></i>Información del Proveedor</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('suppliers.store') }}" method="POST" id="createSupplierForm">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Nombre del proveedor</label>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="contact" class="form-label">Contacto</label>
                                <input type="text" name="contact" id="contact" class="form-control" value="{{ old('contact') }}">
                            </div>

                            

                            <div class="col-md-6">
                                <label for="email" class="form-label">Correo electrónico</label>
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-white border-0 pt-0">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('suppliers.index') }}" class="btn btn-light"><i class="bi bi-x-circle me-2"></i>Cancelar</a>
                        <button type="submit" form="createSupplierForm" class="btn btn-dark"><i class="bi bi-check-circle me-2"></i>Crear Proveedor</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i>Información y Consejos</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Agrega proveedores con información de contacto para facilitar comunicaciones y registros de compras.</p>
                    <div class="d-grid gap-2">
                        <a href="#" class="btn btn-outline-dark" onclick="showSupplierGuide()"><i class="bi bi-question-circle me-2"></i>Guía rápida</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Supplier Guide Modal -->
    <div class="modal fade" id="supplierGuideModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0"><h5 class="modal-title">Guía rápida - Proveedores</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <ul>
                        <li>Incluye nombre oficial del proveedor.</li>
                        <li>Agrega correo y datos de contacto para comunicación.</li>
                        <li>Usa datos consistentes para facilitar búsqueda.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function showSupplierGuide() { new bootstrap.Modal(document.getElementById('supplierGuideModal')).show(); }
    </script>
    @endpush
</div>
@endsection