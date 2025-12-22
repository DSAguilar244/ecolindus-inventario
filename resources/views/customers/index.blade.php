@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Clientes</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Clientes</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <button type="button" id="openCustomerModal" class="btn btn-dark" title="Abrir modal para crear un cliente">Nuevo Cliente</button>
            <a href="{{ route('customers.export.pdf', ['q' => request('q')]) }}" class="btn btn-outline-dark" target="_blank" title="Abrir listado de clientes en PDF">PDF</a>
            <a href="{{ route('customers.export.csv', ['q' => request('q')]) }}" class="btn btn-outline-dark" title="Descargar listado de clientes en CSV">CSV</a>
        </div>
    </div>
    <!-- using global toast from layout -->

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('customers.index') }}" class="row g-2 align-items-end mb-3">
                <div class="col-md-6">
                    <label class="form-label">Buscar</label>
                    <input type="text" id="customer_q" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cédula o nombre" />
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <button class="btn btn-dark" type="button" id="customerSearchBtn">Buscar</button>
                    <button class="btn btn-light" type="button" id="customerClearBtn">Limpiar</button>
                    <a class="btn btn-outline-dark" href="{{ route('customers.index') }}">Refrescar</a>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
        <thead>
            <tr>
                <th>Identificación</th>
                <th>Nombre</th>
                <th>Teléfono</th>
                <th>Email</th>
                <th>Dirección</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @foreach($customers as $c)
            <tr data-customer-id="{{ $c->id }}">
                <td>{{ $c->identification }}</td>
                <td>{{ $c->first_name }} {{ $c->last_name }}</td>
                <td>{{ $c->phone }}</td>
                <td>{{ $c->email }}</td>
                <td>{{ Str::limit($c->address, 60) }}</td>
                <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-dark me-1 customer-edit-btn"
                            data-customer-id="{{ $c->id }}"
                            data-identification="{{ $c->identification }}"
                            data-first_name="{{ $c->first_name }}"
                            data-last_name="{{ $c->last_name }}"
                            data-phone="{{ $c->phone }}"
                            data-email="{{ $c->email }}"
                            data-address="{{ $c->address ?? '' }}"
                        >Editar</button>
                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#customerDeleteModal" data-customer-id="{{ $c->id }}" data-customer-name="{{ $c->first_name }} {{ $c->last_name }}">Eliminar</button>
                </td>
            </tr>
        @endforeach
        </tbody>
                </table>
            </div>
        </div>
    </div>

    {{ $customers->links() }}

{{-- scripts consolidated below --}}
</div>

    <!-- Modal nuevo cliente -->
    <div class="modal fade" id="customerIndexModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Nuevo Cliente</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="customerIndexCreateForm" action="{{ route('customers.store') }}" method="post">
                        @csrf
                        <div id="customerCreateErrors" class="alert alert-danger d-none"></div>
                        <div class="mb-3"><label class="form-label">Identificación</label><input name="identification" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Nombre</label><input name="first_name" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Apellido</label><input name="last_name" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Teléfono</label><input name="phone" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Dirección</label><textarea name="address" class="form-control" rows="2"></textarea></div>
                    </form>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-dark" id="customerIndexCreateSubmit">Crear</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal editar cliente -->
    <div class="modal fade" id="customerEditModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Editar Cliente</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="customerEditForm" method="post">
                        @csrf
                        @method('PUT')
                        <div id="customerEditErrors" class="alert alert-danger d-none"></div>
                        <div class="mb-3"><label class="form-label">Identificación</label><input id="edit_identification" name="identification" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Nombre</label><input id="edit_first_name" name="first_name" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Apellido</label><input id="edit_last_name" name="last_name" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Teléfono</label><input id="edit_phone" name="phone" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Email</label><input id="edit_email" name="email" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Dirección</label><textarea id="edit_address" name="address" class="form-control" rows="2"></textarea></div>
                    </form>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-dark" id="customerEditSubmit">Guardar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Delete Modal -->
    <div class="modal fade" id="customerDeleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Eliminar Cliente</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="bi bi-exclamation-triangle text-danger display-3 mb-4 d-block"></i>
                    <h5 class="mb-3">¿Está seguro que desea eliminar este cliente?</h5>
                    <p class="text-muted mb-0">Cliente:</p>
                    <p class="fw-bold mb-0" id="customerNameToDelete"></p>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form id="customerDeleteForm" action="#" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger">Eliminar Cliente</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

    @push('scripts')
    <script>
        document.getElementById('openCustomerModal').addEventListener('click', function(){
            const m = new bootstrap.Modal(document.getElementById('customerIndexModal'));
            $(document).one('shown.bs.modal', '#customerIndexModal', function(){ $('#customerIndexCreateForm input[name="identification"]').focus(); $('#customerCreateErrors').addClass('d-none').html(''); });
            m.show();
        });
        document.getElementById('customerIndexCreateSubmit').addEventListener('click', async function(){
            const form = document.getElementById('customerIndexCreateForm');
            const res = await ajaxPostForm(form);
            if(res.ok){
                $('#customerIndexModal').modal('hide');
                const cust = res.json.customer;
                const row = `<tr data-customer-id="${cust.id}">
                        <td>${cust.identification}</td>
                        <td>${cust.first_name} ${cust.last_name}</td>
                        <td>${cust.phone ?? ''}</td>
                        <td>${cust.email ?? ''}</td>
                        <td>${cust.address ?? ''}</td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-dark me-1 customer-edit-btn" data-customer-id="${cust.id}" data-identification="${cust.identification}" data-first_name="${cust.first_name}" data-last_name="${cust.last_name}" data-phone="${cust.phone}" data-email="${cust.email}" data-address="${cust.address ?? ''}">Editar</button>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#customerDeleteModal" data-customer-id="${cust.id}" data-customer-name="${cust.first_name} ${cust.last_name}">Eliminar</button>
                        </td>
                    </tr>`;
                $('table.table tbody').prepend(row);
                document.getElementById('customerCreateErrors').classList.add('d-none');
                showGlobalToast('Cliente creado', { classname: 'bg-success text-white', delay: 1500 });
            } else {
                const status = res.resp?.status;
                const json = res.json || {};
                if(status === 409 && json.customer){
                    const cust = json.customer;
                    showGlobalToast('Cliente ya existe: ' + cust.first_name + ' ' + cust.last_name + ' - ' + cust.identification, { classname: 'bg-warning text-dark', delay: 3000 });
                    const row = $(`table.table tbody tr[data-customer-id='${cust.id}']`);
                    if(row.length){ row.addClass('table-warning'); $('html, body').animate({ scrollTop: row.offset().top - 80 }, 400); setTimeout(()=>row.removeClass('table-warning'), 2800); }
                } else if(status === 422 && json.errors){
                    const errors = json.errors || {};
                    const errMessages = Object.values(errors).flat().join('<br>');
                    const el = document.getElementById('customerCreateErrors'); el.classList.remove('d-none'); el.innerHTML = errMessages;
                } else {
                    showGlobalToast('Error al crear cliente', { classname: 'bg-danger text-white', delay: 3000 });
                }
            }
        });
        // delete modal wiring (global)
        document.getElementById('customerDeleteModal')?.addEventListener('show.bs.modal', function(event){
            const button = event.relatedTarget;
            const id = button.getAttribute('data-customer-id');
            const name = button.getAttribute('data-customer-name');
            document.getElementById('customerNameToDelete').textContent = name;
            const form = document.getElementById('customerDeleteForm');
            form.action = `/customers/${id}`;
        });
        // intercept delete form to use AJAX
        const customerDeleteForm = document.getElementById('customerDeleteForm');
        if(customerDeleteForm){
            customerDeleteForm.addEventListener('submit', async function(e){
                e.preventDefault();
                const btn = this.querySelector('button[type="submit"]');
                if(btn) btn.disabled = true;
                const res = await ajaxPostForm(this);
                if(res.ok){
                    $('#customerDeleteModal').modal('hide');
                    const id = this.action.split('/').pop();
                    const row = document.querySelector(`tr[data-customer-id="${id}"]`);
                    if(row) row.remove();
                    showGlobalToast('Cliente eliminado', { classname: 'bg-success text-white', delay: 1200 });
                    setTimeout(function(){ if(document.querySelectorAll('table tbody tr[data-customer-id]').length === 0){ window.location.reload(); } }, 600);
                } else {
                    showGlobalToast(res.json?.message || 'Error al eliminar cliente', { classname: 'bg-danger text-white', delay: 3000 });
                    if(btn) btn.disabled = false;
                }
            });
        }
        // Edit customer
        $(document).on('click', '.customer-edit-btn', function(){
            const button = $(this);
            const id = button.data('customer-id');
            $('#customerEditForm').attr('action', `/customers/${id}`);
            $('#edit_identification').val(button.data('identification'));
            $('#edit_first_name').val(button.data('first_name'));
            $('#edit_last_name').val(button.data('last_name'));
            $('#edit_phone').val(button.data('phone'));
            $('#edit_address').val(button.data('address') ?? '');
            $('#edit_email').val(button.data('email'));
            $('#customerEditErrors').addClass('d-none').html('');
            new bootstrap.Modal(document.getElementById('customerEditModal')).show();
        });

        document.getElementById('customerEditSubmit').addEventListener('click', async function(){
            const form = document.getElementById('customerEditForm');
            const res = await ajaxPostForm(form);
            if(res.ok){
                $('#customerEditModal').modal('hide');
                const cust = res.json.customer;
                const row = $(`table.table tbody tr[data-customer-id='${cust.id}']`);
                if(row.length){
                    row.find('td').eq(0).text(cust.identification);
                    row.find('td').eq(1).text(cust.first_name + ' ' + cust.last_name);
                    row.find('td').eq(2).text(cust.phone ?? '');
                    row.find('td').eq(3).text(cust.email ?? '');
                    row.find('td').eq(4).text(cust.address ?? '');
                    const editBtn = row.find('.customer-edit-btn');
                    editBtn.attr('data-identification', cust.identification);
                    editBtn.attr('data-first_name', cust.first_name);
                    editBtn.attr('data-last_name', cust.last_name);
                    editBtn.attr('data-phone', cust.phone);
                    editBtn.attr('data-email', cust.email);
                    editBtn.attr('data-address', cust.address ?? '');
                }
                showGlobalToast('Cliente actualizado', { classname: 'bg-success text-white', delay: 1500 });
            } else {
                const status = res.resp?.status;
                const json = res.json || {};
                const el = document.getElementById('customerEditErrors');
                if(status === 422 && json.errors){
                    const errors = json.errors || {};
                    const errMessages = Object.values(errors).flat().join('<br>');
                    el.classList.remove('d-none'); el.innerHTML = errMessages;
                } else {
                    el.classList.remove('d-none'); el.innerHTML = 'Error al actualizar cliente';
                }
            }
        });
        // using global showGlobalToast()

        // Search buttons
        $('#customerSearchBtn').on('click', function(){
            const q = $('#customer_q').val().trim();
            const url = new URL(window.location);
            if(q){ url.searchParams.set('q', q); } else { url.searchParams.delete('q'); }
            window.location = url.toString();
        });
        $('#customerClearBtn').on('click', function(){
            const url = new URL(window.location);
            url.searchParams.delete('q');
            window.location = url.toString();
        });
    </script>
    @endpush
