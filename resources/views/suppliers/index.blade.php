@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Proveedores</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Proveedores</li>
                </ol>
            </nav>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('suppliers.create') }}" class="btn btn-dark">
                <i class="bi bi-plus-circle me-2"></i>Nuevo Proveedor
            </a>
            <a href="{{ route('suppliers.export.pdf') }}" class="btn btn-outline-dark">
                <i class="bi bi-file-pdf me-2"></i>Exportar PDF
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center border-0 shadow-sm">
            <i class="bi bi-check-circle-fill me-2"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 ps-4">Proveedor</th>
                            <th class="border-0">Contacto</th>
                            <th class="border-0">Email</th>
                            <th class="border-0 text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $supplier)
                        <tr>
                            <td class="ps-4">
                                <div>
                                    <h6 class="mb-1">{{ $supplier->name }}</h6>
                                    @if($supplier->contact)
                                        <small class="text-muted">Contacto: {{ $supplier->contact }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $supplier->contact ?? '-' }}</td>
                            <td>{{ $supplier->email ?? '-' }}</td>
                            <td class="text-end pe-4">
                                <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-sm btn-outline-dark me-1">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        data-bs-toggle="modal" data-bs-target="#deleteModal"
                                        data-supplier-id="{{ $supplier->id }}"
                                        data-supplier-name="{{ $supplier->name }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <i class="bi bi-inbox display-1 text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-0">No hay proveedores registrados.</p>
                                <a href="{{ route('suppliers.create') }}" class="btn btn-dark mt-3">
                                    <i class="bi bi-plus-circle me-2"></i>Agregar Proveedor
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if ($suppliers->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-4">
        <p class="text-muted mb-0">Mostrando {{ $suppliers->firstItem() }} a {{ $suppliers->lastItem() }} de {{ $suppliers->total() }} proveedores</p>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item {{ $suppliers->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $suppliers->previousPageUrl() }}" aria-label="Previous"><i class="bi bi-chevron-left"></i></a>
                </li>
                @foreach ($suppliers->links()->elements[0] as $page => $url)
                    @if ($page == $suppliers->currentPage())
                        <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                    @endif
                @endforeach
                <li class="page-item {{ !$suppliers->hasMorePages() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $suppliers->nextPageUrl() }}" aria-label="Next"><i class="bi bi-chevron-right"></i></a>
                </li>
            </ul>
        </nav>
    </div>
    @endif

    <!-- Delete Modal -->
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
                    <p class="fw-bold mb-0" id="supplierNameToDelete"></p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <form id="deleteForm" action="" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i>Eliminar Proveedor
                        </button>
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
                form.action = `/suppliers/${supplierId}`;
                nameEl.textContent = supplierName;
            });
        });
    </script>
    @endpush
</div>
@endsection