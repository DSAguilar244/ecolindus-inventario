@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <h2>Administración de usuarios</h2>
    
    <!-- VISTA DESKTOP: Tabla -->
    <div class="card mt-3 d-none d-lg-block">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($users as $u)
                        <tr data-user-id="{{ $u->id }}">
                            <td>{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td>
                                <span class="badge" :class="'{{ $u->role ?? 'viewer' }}' === 'admin' ? 'bg-danger' : ('{{ $u->role ?? 'viewer' }}' === 'editor' ? 'bg-warning' : 'bg-secondary')">
                                    {{ $u->role ?? 'viewer' }}
                                </span>
                            </td>
                            <td class="text-center text-nowrap">
                                <form action="{{ route('admin.users.update', $u) }}" method="POST" class="d-inline">
                                    @csrf @method('PATCH')
                                    <select name="role" class="form-select form-select-sm d-inline-block w-auto"> 
                                        <option value="viewer" {{ $u->role === 'viewer' ? 'selected' : '' }}>Viewer</option>
                                        <option value="editor" {{ $u->role === 'editor' ? 'selected' : '' }}>Editor</option>
                                        <option value="admin" {{ $u->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                    </select>
                                    <button class="btn btn-sm btn-primary" style="height: 34px; min-width: 44px;">Guardar</button>
                                </form>
                                @can('manage-users')
                                @if(Auth::id() !== $u->id)
                                    <button type="button" class="btn btn-sm btn-danger user-delete-btn" data-user-id="{{ $u->id }}" data-user-name="{{ $u->name }}" data-bs-toggle="modal" data-bs-target="#userDeleteModal" style="height: 34px; min-width: 44px;" title="Eliminar usuario">🗑️</button>
                                @endif
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- VISTA MOBILE: Cards -->
    <div class="row g-3 d-lg-none">
    @forelse($users as $u)
        <div class="col-12">
            <div class="card border-light shadow-sm">
                <div class="card-body p-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-12">
                            <h6 class="mb-1 fw-bold">{{ $u->name }}</h6>
                            <p class="text-muted small mb-2">{{ $u->email }}</p>
                            <div class="mb-3">
                                <span class="badge" :class="'{{ $u->role ?? 'viewer' }}' === 'admin' ? 'bg-danger' : ('{{ $u->role ?? 'viewer' }}' === 'editor' ? 'bg-warning' : 'bg-secondary')">
                                    {{ $u->role ?? 'viewer' }}
                                </span>
                            </div>
                        </div>
                        <div class="col-12">
                            <form action="{{ route('admin.users.update', $u) }}" method="POST" class="d-flex gap-2 flex-wrap">
                                @csrf @method('PATCH')
                                <select name="role" class="form-select form-select-sm flex-grow-1" style="min-height: 44px;"> 
                                    <option value="viewer" {{ $u->role === 'viewer' ? 'selected' : '' }}>Viewer</option>
                                    <option value="editor" {{ $u->role === 'editor' ? 'selected' : '' }}>Editor</option>
                                    <option value="admin" {{ $u->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                </select>
                                <button class="btn btn-sm btn-primary" style="height: 44px; min-width: 44px;">Guardar</button>
                            </form>
                        </div>
                        @can('manage-users')
                        @if(Auth::id() !== $u->id)
                        <div class="col-12">
                            <button type="button" class="btn btn-sm btn-danger w-100 user-delete-btn" style="height: 44px;" data-user-id="{{ $u->id }}" data-user-name="{{ $u->name }}" data-bs-toggle="modal" data-bs-target="#userDeleteModal">
                                🗑️ Eliminar usuario
                            </button>
                        </div>
                        @endif
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info">No hay usuarios registrados.</div>
        </div>
    @endforelse
    </div>
            <!-- Modal eliminar usuario -->
            <div class="modal fade" id="userDeleteModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header border-0">
                            <h5 class="modal-title">Eliminar Usuario</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center py-4">
                            <i class="bi bi-exclamation-triangle text-danger display-3 mb-4 d-block"></i>
                            <h5 class="mb-3">¿Está seguro que desea eliminar este usuario?</h5>
                            <p class="text-muted mb-0">Usuario:</p>
                            <p class="fw-bold mb-0" id="userToDeleteName"></p>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                            <form id="userDeleteForm" action="" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger"><i class="bi bi-trash me-2"></i>Eliminar Usuario</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function(){
        const modal = document.getElementById('userDeleteModal');
        const userDeleteForm = document.getElementById('userDeleteForm');

        if(modal && userDeleteForm){
            document.querySelectorAll('.user-delete-btn').forEach(function(btn){
                btn.addEventListener('click', function(){
                    const userId = btn.getAttribute('data-user-id');
                    const userName = btn.getAttribute('data-user-name');
                    const nameEl = modal.querySelector('#userToDeleteName');
                    if(nameEl) nameEl.textContent = userName;
                    userDeleteForm.action = `/admin/users/${userId}`;
                });
            });

            userDeleteForm.addEventListener('submit', async function(e){
                e.preventDefault();
                const btn = userDeleteForm.querySelector('button[type="submit"]');
                if(btn) btn.disabled = true;
                
                try {
                    const response = await fetch(userDeleteForm.action, {
                        credentials: 'same-origin',
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': userDeleteForm.querySelector('input[name="_token"]').value,
                            'Accept': 'application/json'
                        },
                        body: new FormData(userDeleteForm)
                    });

                    if(response.ok){
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        if(bsModal) bsModal.hide();
                        
                        const userId = userDeleteForm.action.split('/').pop();
                        const row = document.querySelector(`tr[data-user-id="${userId}"]`);
                        const card = document.querySelector(`.card-body [data-user-id="${userId}"]`).closest('.col-12');
                        
                        if (row) { row.remove(); }
                        if (card) { card.remove(); }
                        
                        showGlobalToast('Usuario eliminado correctamente', { classname: 'bg-success text-white', delay: 1200 });
                        
                        setTimeout(function() {
                            if (document.querySelectorAll('table tbody tr[data-user-id]').length === 0) {
                                window.location.reload();
                            }
                        }, 600);
                    } else {
                        const data = await response.json();
                        showGlobalToast(data?.message || 'Error al eliminar usuario', { classname: 'bg-danger text-white', delay: 3000 });
                        if(btn) btn.disabled = false;
                    }
                } catch(error) {
                    showGlobalToast('Error de red.', { classname: 'bg-danger text-white', delay: 3000 });
                    if(btn) btn.disabled = false;
                }
            });
        }
    });
</script>
@endpush
