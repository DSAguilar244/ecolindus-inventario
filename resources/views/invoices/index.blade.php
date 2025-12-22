@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Ventas / Facturas</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Facturas</li>
                </ol>
            </nav>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('invoices.create') }}" class="btn btn-dark">
                <i class="bi bi-plus-circle me-2"></i>Nueva Factura
            </a>
            <a href="{{ route('invoices.export.pdf', request()->query()) }}" class="btn btn-outline-dark" target="_blank">
                <i class="bi bi-file-pdf me-2"></i>Exportar PDF
            </a>
        </div>
    </div>

    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function(){
                showGlobalToast(@json(session('success')), { classname: 'bg-success text-white', delay: 2000 });
            });
        </script>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('invoices.index') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Cliente</label>
                    <select id="filter-customer-select" name="customer_id" class="form-select" style="width:100%">
                        <option value="">Todos los clientes</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->first_name }} {{ $c->last_name }} - {{ $c->identification }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha desde</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" />
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha hasta</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control" />
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-dark">Filtrar</button>
                    <a href="{{ route('invoices.index') }}" class="btn btn-light">Limpiar</a>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="pendiente" {{ request('status') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="emitida" {{ request('status') === 'emitida' ? 'selected' : '' }}>Emitida</option>
                        <option value="anulada" {{ request('status') === 'anulada' ? 'selected' : '' }}>Anulada</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
                <div class="table-responsive">
                <table id="invoices-table" class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 ps-4">#</th>
                            <th class="border-0">Cliente</th>
                            <th class="border-0">Total</th>
                            <th class="border-0">Fecha</th>
                            <th class="border-0">Estado</th>
                            <th class="border-0 text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $inv)
                        <tr data-invoice-id="{{ $inv->id }}">
                            <td class="ps-4">{{ $inv->invoice_number }}</td>
                            <td>
                                <div>
                                    <h6 class="mb-0">{{ optional($inv->customer)->first_name }} {{ optional($inv->customer)->last_name }}</h6>
                                    <small class="text-muted">{{ optional($inv->customer)->identification }}</small>
                                </div>
                            </td>
                            <td>{{ number_format($inv->total,2) }}</td>
                            <td>{{ $inv->date->format('Y-m-d') }}</td>
                            <td>
                                @if($inv->status === \App\Models\Invoice::STATUS_ANULADA)
                                    <span class="badge bg-danger">Anulada</span>
                                @elseif($inv->status === \App\Models\Invoice::STATUS_EMITIDA)
                                    <span class="badge bg-success">Emitida</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($inv->status) }}</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                {{-- Edit link handled below, avoid duplicate --}}
                                    <a href="{{ route('invoices.show', $inv) }}" class="btn btn-sm btn-outline-dark me-1" title="Ver">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($inv->status === \App\Models\Invoice::STATUS_PENDIENTE)
                                        <a href="{{ route('invoices.edit', $inv) }}" class="btn btn-sm btn-outline-primary me-1" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @else
                                        @can('edit-emitted-invoice')
                                            <a href="{{ route('invoices.edit', $inv) }}" class="btn btn-sm btn-outline-primary me-1" title="Editar">
                                                <i class="bi bi-pencil"></i> Editar
                                            </a>
                                        @endcan
                                    @endif
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteModal"
                                            data-invoice-id="{{ $inv->id }}"
                                            data-invoice-number="{{ $inv->invoice_number }}"
                                            title="Anular">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                        <!-- Reuse the existing delete modal trigger; the permanent delete is separate if admin. -->
                                    @can('force-delete-invoice')
                                        <button type="button" class="btn btn-outline-danger btn-sm force-delete-btn" data-bs-toggle="modal" data-bs-target="#forceDeleteModal" data-invoice-id="{{ $inv->id }}" data-invoice-number="{{ $inv->invoice_number }}">Eliminar</button>
                                    @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="bi bi-receipt display-1 text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-0">No hay facturas registradas.</p>
                                <a href="{{ route('invoices.create') }}" class="btn btn-dark mt-3">
                                    <i class="bi bi-plus-circle me-2"></i>Nueva Factura
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if ($invoices->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-4">
        <p class="text-muted mb-0">
            Mostrando {{ $invoices->firstItem() }} a {{ $invoices->lastItem() }} de {{ $invoices->total() }} facturas
        </p>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item {{ $invoices->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $invoices->previousPageUrl() }}" aria-label="Previous">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>
                @foreach ($invoices->links()->elements[0] as $page => $url)
                    @if ($page == $invoices->currentPage())
                        <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                    @endif
                @endforeach
                <li class="page-item {{ !$invoices->hasMorePages() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $invoices->nextPageUrl() }}" aria-label="Next">
                        <i class="bi bi-chevron-right"></i>
                    </a>
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
                    <h5 class="modal-title">Anular Factura</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="bi bi-exclamation-triangle text-danger display-3 mb-4 d-block"></i>
                    <h5 class="mb-3">¿Está seguro que desea anular esta factura?</h5>
                    <p class="text-muted mb-0">Factura:</p>
                    <p class="fw-bold mb-0" id="invoiceNumberToDelete"></p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <form id="deleteForm" action="" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i>Anular Factura
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @can('force-delete-invoice')
    <!-- Global Permanent Delete Modal (single modal used for all invoices) -->
    <div class="modal fade" id="forceDeleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Eliminar Factura Permanentemente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="bi bi-exclamation-triangle text-danger display-3 mb-4 d-block"></i>
                    <h5 class="mb-3">¿Está seguro que desea eliminar esta factura de manera permanente?</h5>
                    <p class="text-muted mb-0">Factura:</p>
                    <p class="fw-bold mb-0" id="forceDeleteInvoiceNumber"></p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <form id="forceDeleteForm" action="" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <div class="mb-3">
                            <label class="form-label">Motivo (opcional)</label>
                            <textarea name="audit_reason" class="form-control" rows="2" placeholder="Puede indicar el motivo de eliminación"></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i>Eliminar Permanentemente
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endcan

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal de eliminación normal
            const deleteModal = document.getElementById('deleteModal');
            if(deleteModal) {
                let deleteModalScrollTop = 0;
                deleteModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const invoiceId = button.getAttribute('data-invoice-id');
                    const invoiceNumber = button.getAttribute('data-invoice-number');
                    const form = this.querySelector('#deleteForm');
                    const invoiceNumberElement = this.querySelector('#invoiceNumberToDelete');
                    form.action = `/invoices/${invoiceId}`;
                    form.dataset.invoiceId = invoiceId;
                    invoiceNumberElement.textContent = invoiceNumber;
                    // Ensure submit button is enabled when modal opens (in case it was disabled previously)
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Anular Factura'; }
                });
                deleteModal.addEventListener('shown.bs.modal', function(){ deleteModalScrollTop = window.scrollY; });
                deleteModal.addEventListener('hidden.bs.modal', function(){ window.scrollTo(0, deleteModalScrollTop); });
                // intercept normal delete (anular) form via AJAX
                const deleteForm = deleteModal.querySelector('#deleteForm');
                if(deleteForm){
                    deleteForm.addEventListener('submit', function(e){
                        e.preventDefault();
                        const btn = deleteForm.querySelector('button[type="submit"]');
                        if(btn) btn.disabled = true;
                        fetch(deleteForm.action, {
                            method: 'POST',
                               credentials: 'same-origin',
                            headers: {
                                'X-CSRF-TOKEN': deleteForm.querySelector('input[name="_token"]').value,
                                'Accept': 'application/json'
                            },
                            body: new FormData(deleteForm)
                        }).then(resp => {
                            if(resp.ok){
                                // Close modal and show success
                                const bsModal = bootstrap.Modal.getInstance(deleteModal);
                                if(bsModal) bsModal.hide();
                                let container = document.querySelector('.container-fluid.py-4');
                                if(!container) container = document.querySelector('body');
                                showGlobalToast('Factura anulada', { classname: 'bg-success text-white', delay: 1500 });
                                // Remove the row from the list (AJAX deletion so no reload) and show toast
                                const invoiceId = deleteForm.dataset.invoiceId;
                                const row = document.querySelector(`tr[data-invoice-id="${invoiceId}"]`);
                                if(row){ row.remove(); }
                                // If after removal we have no rows left in table, reload to update pagination
                                setTimeout(function(){
                                    if(document.querySelectorAll('#invoices-table tbody tr').length === 0){ window.location.reload(); }
                                }, 600);
                            } else {
                                resp.json().then(data => { showGlobalToast(data?.message || 'Error al anular factura', { classname: 'bg-danger text-white', delay: 3000 }); });
                                if(btn) btn.disabled = false;
                            }
                        }).catch(()=>{ showGlobalToast('Error de red.', { classname: 'bg-danger text-white', delay: 3000 }); if(btn) btn.disabled = false; });
                    });
                }
            }

            // Global modal de eliminación permanente (admin)
            const forceModal = document.getElementById('forceDeleteModal');
            if(forceModal){
                let forceDeleteScrollTop = 0;
                const form = forceModal.querySelector('form');
                forceModal.addEventListener('show.bs.modal', function(event){
                    const button = event.relatedTarget;
                    const invoiceId = button.getAttribute('data-invoice-id');
                    const invoiceNumber = button.getAttribute('data-invoice-number');
                    form.action = `/invoices/${invoiceId}/force`;
                    form.dataset.invoiceId = invoiceId;
                    const invoiceNumberEl = document.getElementById('forceDeleteInvoiceNumber');
                    if(invoiceNumberEl) invoiceNumberEl.textContent = invoiceNumber;
                });
                form.addEventListener('submit', function(e){
                        // Mostrar mensaje de éxito y redirigir suavemente
                        e.preventDefault();
                        // Opcional: puedes mostrar un spinner o deshabilitar el botón
                        const btn = form.querySelector('button[type="submit"]');
                        if(btn) btn.disabled = true;
                        // Enviar el formulario vía AJAX
                        fetch(form.action, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                                'Accept': 'application/json',
                            },
                            body: new FormData(form)
                        }).then(resp => {
                            if(resp.ok){
                                // Cerrar el modal
                                const bsModal = bootstrap.Modal.getInstance(forceModal);
                                if(bsModal) bsModal.hide();
                                window.scrollTo(0, forceDeleteScrollTop);
                                // Mostrar alerta de éxito
                                let container = document.querySelector('.container-fluid.py-4');
                                if(!container) container = document.querySelector('body');
                                showGlobalToast('Factura eliminada correctamente', { classname: 'bg-success text-white', delay: 1500 });
                                const invId = form.dataset.invoiceId;
                                const row = document.querySelector(`tr[data-invoice-id="${invId}"]`);
                                if(row){ row.remove(); }
                                setTimeout(function(){ if(document.querySelectorAll('#invoices-table tbody tr').length === 0){ window.location.reload(); } }, 600);
                            }else{
                                resp.json().then(data => { showGlobalToast(data?.error || 'Error al eliminar la factura', { classname: 'bg-danger text-white', delay: 3000 }); });
                                if(btn) btn.disabled = false;
                            }
                        }).catch(()=>{
                            showGlobalToast('Error de red.', { classname: 'bg-danger text-white', delay: 3000 });
                            if(btn) btn.disabled = false;
                        });
                    });
                forceModal.addEventListener('shown.bs.modal', function(){ forceDeleteScrollTop = window.scrollY; });
                forceModal.addEventListener('hidden.bs.modal', function(){ window.scrollTo(0, forceDeleteScrollTop); });
            }

            // Initialize Select2 for filter customer
            $('#filter-customer-select').select2({
                ajax: {
                    url: '{{ route('customers.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params){ return { q: params.term }; },
                    processResults: function(data){ return { results: data.results }; }
                },
                minimumInputLength: 0,
                allowClear: true
            });
            @if(request('customer_id') && $customers->where('id', request('customer_id'))->first())
                var existing = {id: '{{ request('customer_id') }}', text: '{{ $customers->where('id', request('customer_id'))->first()->first_name }} {{ $customers->where('id', request('customer_id'))->first()->last_name }} - {{ $customers->where('id', request('customer_id'))->first()->identification }}'};
                $('#filter-customer-select').append(new Option(existing.text, existing.id, true, true)).trigger('change');
            @endif
        });
    </script>
    @endpush
</div>
@endsection
