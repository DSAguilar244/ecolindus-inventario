@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Marcas</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Marcas</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('brands.create') }}" class="btn btn-dark">
                <i class="bi bi-plus-circle me-2"></i>Nueva Marca
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
        <thead>
                        <tr>
                            <th class="border-0 ps-4">Nombre</th>
                            <th class="border-0">Descripción</th>
                            <th class="border-0 text-end pe-4"></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($brands as $b)
                        <tr data-brand-id="{{ $b->id }}">
                            <td class="ps-4">{{ $b->name }}</td>
                            <td>{{ Str::limit($b->description, 80) }}</td>
                            <td class="text-end pe-4">
                                <a href="{{ route('brands.edit', $b) }}" class="btn btn-sm btn-outline-dark me-1">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger brand-delete-btn" data-brand-id="{{ $b->id }}" data-brand-name="{{ $b->name }}"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{ $brands->links() }}

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteBrandModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Eliminar Marca</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="bi bi-exclamation-triangle text-danger display-3 mb-4 d-block"></i>
                    <h5 class="mb-3">¿Está seguro que desea eliminar esta marca?</h5>
                    <p class="text-muted mb-0">Esta acción eliminará permanentemente la marca:</p>
                    <p class="fw-bold mb-0" id="brandNameToDelete"></p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="bi bi-trash me-2"></i>Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function(){
        let brandToDelete = null;
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteBrandModal'));
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

        document.querySelectorAll('.brand-delete-btn').forEach(btn => {
            btn.addEventListener('click', function(){
                brandToDelete = {
                    id: this.getAttribute('data-brand-id'),
                    name: this.getAttribute('data-brand-name')
                };
                document.getElementById('brandNameToDelete').textContent = brandToDelete.name;
                deleteModal.show();
            });
        });

        confirmDeleteBtn.addEventListener('click', async function(){
            if(!brandToDelete) return;
            const id = brandToDelete.id;
            confirmDeleteBtn.disabled = true;
            confirmDeleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Eliminando...';
            
            try {
                const token = document.querySelector('meta[name="csrf-token"]').content;
                const resp = await fetch(`/brands/${id}`, {
                    method: 'DELETE',
                    credentials: 'same-origin',
                    headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
                });
                if(resp.ok){
                    deleteModal.hide();
                    const row = document.querySelector(`tr[data-brand-id="${id}"]`);
                    if(row) row.remove();
                    showGlobalToast('Marca eliminada', { classname: 'bg-success text-white', delay: 1200 });
                    setTimeout(function(){ if(document.querySelectorAll('table tbody tr[data-brand-id]').length === 0){ window.location.reload(); } }, 600);
                } else {
                    const data = await resp.json();
                    showGlobalToast(data?.message || 'Error al eliminar', { type: 'error' });
                    confirmDeleteBtn.disabled = false;
                    confirmDeleteBtn.innerHTML = '<i class="bi bi-trash me-2"></i>Eliminar';
                }
            } catch(err){
                showGlobalToast('Error de red', { type: 'error' });
                confirmDeleteBtn.disabled = false;
                confirmDeleteBtn.innerHTML = '<i class="bi bi-trash me-2"></i>Eliminar';
            }
        });
    });
</script>
@endpush
@endsection
