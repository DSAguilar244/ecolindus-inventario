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
    <!-- Toast container -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
        <div id="customerToast" class="toast align-items-center text-white bg-dark border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="customerToastMessage">Acción realizada</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

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
        $('#customerIndexCreateSubmit').on('click', function(){
            const form = $('#customerIndexCreateForm');
            $.post(form.attr('action'), form.serialize())
                .done(function(res){
                    $('#customerIndexModal').modal('hide');
                    // prepend new row to table
                    const row = `<tr data-customer-id="${res.customer.id}">
                        <td>${res.customer.identification}</td>
                        <td>${res.customer.first_name} ${res.customer.last_name}</td>
                        <td>${res.customer.phone ?? ''}</td>
                        <td>${res.customer.email ?? ''}</td>
                        <td>${res.customer.address ?? ''}</td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-dark me-1 customer-edit-btn" data-customer-id="${res.customer.id}" data-identification="${res.customer.identification}" data-first_name="${res.customer.first_name}" data-last_name="${res.customer.last_name}" data-phone="${res.customer.phone}" data-email="${res.customer.email}">Editar</button>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#customerDeleteModal" data-customer-id="${res.customer.id}" data-customer-name="${res.customer.first_name} ${res.customer.last_name}">Eliminar</button>
                        </td>
                    </tr>`;
                        $('table.table tbody').prepend(row);
                        $('#customerCreateErrors').addClass('d-none').html('');
                        showCustomerToast('Cliente creado');
                })
                        .fail(function(xhr){
                                    if(xhr.status === 409 && xhr.responseJSON && xhr.responseJSON.customer){
                                        const cust = xhr.responseJSON.customer;
                                        showCustomerToast('Cliente ya existe: ' + cust.first_name + ' ' + cust.last_name + ' - ' + cust.identification);
                                        // scroll to existing row and highlight
                                        const row = $(`table.table tbody tr[data-customer-id='${cust.id}']`);
                                        if(row.length){
                                            row.addClass('table-warning');
                                            $('html, body').animate({ scrollTop: row.offset().top - 80 }, 400);
                                            setTimeout(()=>row.removeClass('table-warning'), 2800);
                                        }
                                    } else if(xhr.status === 422){
                                        let errors = xhr.responseJSON?.errors || {};
                                        let errMessages = Object.values(errors).flat().join('<br>');
                                        $('#customerCreateErrors').removeClass('d-none').html(errMessages);
                                    } else {
                                        showCustomerToast('Error al crear cliente');
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
        });
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

        $('#customerEditSubmit').on('click', function(){
            const form = $('#customerEditForm');
            const action = form.attr('action');
            const data = form.serialize();
            $.ajax({
                url: action,
                type: 'POST', // using POST because of method override in form
                data: data,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            }).done(function(res){
                $('#customerEditModal').modal('hide');
                const cust = res.customer;
                // find the table row by customer id
                const row = $(`table.table tbody tr[data-customer-id='${cust.id}']`);
                if(row.length){
                    row.find('td').eq(0).text(cust.identification);
                    row.find('td').eq(1).text(cust.first_name + ' ' + cust.last_name);
                    row.find('td').eq(2).text(cust.phone ?? '');
                    row.find('td').eq(3).text(cust.email ?? '');
                    row.find('td').eq(4).text(cust.address ?? '');
                    // update edit button data attributes
                    const editBtn = row.find('.customer-edit-btn');
                    editBtn.attr('data-identification', cust.identification);
                    editBtn.attr('data-first_name', cust.first_name);
                    editBtn.attr('data-last_name', cust.last_name);
                    editBtn.attr('data-phone', cust.phone);
                    editBtn.attr('data-email', cust.email);
                    editBtn.attr('data-address', cust.address ?? '');
                }
                showCustomerToast('Cliente actualizado');
            }).fail(function(xhr){
                if(xhr.status === 422){
                    let errors = xhr.responseJSON?.errors || {};
                    let errMessages = Object.values(errors).flat().join('<br>');
                    $('#customerEditErrors').removeClass('d-none').html(errMessages);
                } else {
                    $('#customerEditErrors').removeClass('d-none').html('Error al actualizar cliente');
                }
            });
        });
        // helper: toast
        function showCustomerToast(message){
            $('#customerToastMessage').text(message);
            const tEl = document.getElementById('customerToast');
            const toast = new bootstrap.Toast(tEl);
            toast.show();
        }

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
