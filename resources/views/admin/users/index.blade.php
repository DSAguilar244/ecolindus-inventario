@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <h2>Administración de usuarios</h2>
    <div class="card mt-3">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                @foreach($users as $u)
                    <tr data-user-id="{{ $u->id }}">
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->role ?? 'viewer' }}</td>
                        <td>
                            <form action="{{ route('admin.users.update', $u) }}" method="POST" class="d-inline">
                                @csrf @method('PATCH')
                                <select name="role" class="form-select d-inline-block w-auto"> 
                                    <option value="viewer" {{ $u->role === 'viewer' ? 'selected' : '' }}>Viewer</option>
                                    <option value="editor" {{ $u->role === 'editor' ? 'selected' : '' }}>Editor</option>
                                    <option value="admin" {{ $u->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                </select>
                                <button class="btn btn-sm btn-primary">Guardar</button>
                            </form>
                            @can('manage-users')
                            @if(Auth::id() !== $u->id)
                                <button type="button" class="btn btn-sm btn-danger user-delete-btn ms-2" data-user-id="{{ $u->id }}" data-user-name="{{ $u->name }}" data-bs-toggle="modal" data-bs-target="#userDeleteModal"><i class="bi bi-trash"></i> Eliminar</button>
                            @endif
                            @endcan
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
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
        if(modal){
            modal.addEventListener('show.bs.modal', function(e){
                // handled by button click below
            });
        }

        document.querySelectorAll('.user-delete-btn').forEach(function(btn){
            btn.addEventListener('click', function(){
                const userId = btn.getAttribute('data-user-id');
                const userName = btn.getAttribute('data-user-name');
                const modalEl = document.getElementById('userDeleteModal');
                const nameEl = modalEl.querySelector('#userToDeleteName');
                const form = document.getElementById('userDeleteForm');
                if(nameEl) nameEl.textContent = userName;
                if(form) form.action = `/admin/users/${userId}`;
            });
        });

        const userDeleteForm = document.getElementById('userDeleteForm');
        if (userDeleteForm) {
            userDeleteForm.addEventListener('submit', function(e){
                e.preventDefault();
                const btn = userDeleteForm.querySelector('button[type="submit"]');
                if(btn) btn.disabled = true;
                fetch(userDeleteForm.action, {
                    credentials: 'same-origin',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': userDeleteForm.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json'
                    },
                    body: new FormData(userDeleteForm)
                }).then(resp => {
                    if(resp.ok){
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        if(bsModal) bsModal.hide();
                        showGlobalToast('Usuario eliminado correctamente', { classname: 'bg-success text-white', delay: 1200 });
                        // remove user row from DOM and reload if no rows left to keep pagination consistent
                        const uid = userDeleteForm.action.split('/').pop();
                        const row = document.querySelector(`tr[data-user-id="${uid}"]`);
                        if (row) { row.remove(); }
                        setTimeout(function() { if (document.querySelectorAll('table tbody tr[data-user-id]').length === 0) { window.location.reload(); } }, 600);
                    }else{
                        resp.json().then(data => alert(data?.message || 'Error al eliminar usuario'));
                        if(btn) btn.disabled = false;
                    }
                }).catch(()=>{ alert('Error de red.'); if(btn) btn.disabled = false; });
            });
        }
    });
</script>
@endpush
