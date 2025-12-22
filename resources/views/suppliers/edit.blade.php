@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Editar Proveedor</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('suppliers.index') }}" class="text-decoration-none">Proveedores</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Editar</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0"><i class="bi bi-pencil-square me-2"></i>Editar información</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('suppliers.update', $supplier) }}" method="POST" id="editSupplierForm">
                        @csrf @method('PUT')
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Nombre del proveedor</label>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $supplier->name) }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="contact" class="form-label">Contacto</label>
                                <input type="text" name="contact" id="contact" class="form-control" value="{{ old('contact', $supplier->contact) }}">
                            </div>

                            

                            <div class="col-md-6">
                                <label for="email" class="form-label">Correo electrónico</label>
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $supplier->email) }}">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-white border-0 pt-0">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('suppliers.index') }}" class="btn btn-light"><i class="bi bi-x-circle me-2"></i>Cancelar</a>
                        <button type="submit" form="editSupplierForm" class="btn btn-dark"><i class="bi bi-check-circle me-2"></i>Guardar cambios</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
                    <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i>Detalles del proveedor</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Nombre:</strong> {{ $supplier->name }}</p>
                    <p class="mb-1"><strong>Contacto:</strong> {{ $supplier->contact ?? '-' }}</p>
                            <p class="mb-1"><strong>Email:</strong> {{ $supplier->email ?? '-' }}</p>
                        </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0"><i class="bi bi-lightning me-2"></i>Acciones Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-supplier-id="{{ $supplier->id }}" data-supplier-name="{{ $supplier->name }}">
                            <i class="bi bi-trash me-2"></i>Eliminar Proveedor
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal (reuse same modal markup as index) -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Eliminar Proveedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="bi bi-exclamation-triangle text-danger display-3 mb-4 d-block"></i>
                    <h5 class="mb-3">¿Está seguro que desea eliminar este proveedor?</h5>
                    <p class="text-muted mb-0">Esta acción eliminará permanentemente el proveedor:</p>
                    <p class="fw-bold mb-0" id="supplierNameToDelete">{{ $supplier->name }}</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <form id="deleteForm" action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger"><i class="bi bi-trash me-2"></i>Eliminar Proveedor</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteModal = document.getElementById('deleteModal');
            deleteModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const supplierId = button.getAttribute('data-supplier-id');
                const supplierName = button.getAttribute('data-supplier-name');
                const form = this.querySelector('#deleteForm');
                const nameEl = this.querySelector('#supplierNameToDelete');
                if (form) form.action = `/suppliers/${supplierId}`;
                if (nameEl) nameEl.textContent = supplierName;
            });
        });
    </script>
    @endpush
</div>
@endsection