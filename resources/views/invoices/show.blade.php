@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Factura {{ $invoice->invoice_number }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}" class="text-decoration-none">Facturas</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Ver</li>
                </ol>
            </nav>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('invoices.print', $invoice) }}" class="btn btn-outline-dark">
                <i class="bi bi-file-earmark-pdf me-2"></i>Imprimir / PDF
            </a>
            <a href="{{ route('invoices.index') }}" class="btn btn-link">Volver a facturas</a>

            @if($invoice->status === \App\Models\Invoice::STATUS_ANULADA)
                <form action="{{ route('invoices.reopen', $invoice) }}" method="POST" style="display:inline">
                    @csrf
                    <button class="btn btn-warning">Reabrir factura</button>
                </form>
            @else
                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">Anular factura</button>
            @endif
            @if($invoice->status === \App\Models\Invoice::STATUS_PENDIENTE)
                <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-primary">Editar</a>
            @elseif(auth()->check() && auth()->user()->can('edit-emitted-invoice'))
                {{-- Allow admins to edit emitted invoices (with caution) --}}
                <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-primary">Editar</a>
            @endif
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <h6 class="mb-1">Cliente</h6>
                    <p class="mb-0">{{ optional($invoice->customer)->first_name }} {{ optional($invoice->customer)->last_name }}</p>
                    <small class="text-muted">{{ optional($invoice->customer)->identification }}</small>
                    @if(optional($invoice->customer)->address)
                        <p class="mb-0 mt-1 small">{{ optional($invoice->customer)->address }}</p>
                    @endif
                </div>
                <div class="col-md-6 text-end">
                    <h6 class="mb-1">Fecha</h6>
                    <p class="mb-0">{{ $invoice->date->format('Y-m-d H:i') }}</p>
                    <div class="d-flex align-items-center justify-content-end gap-2">
                        <small class="text-muted"># {{ $invoice->invoice_number }}</small>
                        @if(auth()->check() && (auth()->user()->can('edit-emitted-invoice') || $invoice->status === \App\Models\Invoice::STATUS_PENDIENTE))
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editInvoiceNumberModal">
                                <i class="bi bi-pencil"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <h6 class="mt-3">Artículos</h6>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Impuesto</th><th class="text-end">Total</th></tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                        <tr>
                            <td>{{ optional($item->product)->name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->unit_price,2) }}</td>
                            <td>{{ $item->tax_rate }}% <small class="text-muted">({{ number_format((($item->tax_rate ?? 0)/100) * $item->line_total, 2) }})</small></td>
                            <td class="text-end">{{ number_format($item->line_total + ((($item->tax_rate ?? 0)/100) * $item->line_total),2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="row mt-3">
                <div class="col-6">
                    @if($invoice->notes)
                        <h6>Observaciones</h6>
                        <p class="text-muted">{{ $invoice->notes }}</p>
                    @endif
                </div>
                <div class="col-6 text-end">
                    <p class="mb-0">Subtotal: {{ number_format($invoice->subtotal,2) }}</p>
                    <p class="mb-0">Impuesto: {{ number_format($invoice->tax_total,2) }}</p>
                    <h4>Total: {{ number_format($invoice->total,2) }}</h4>
                </div>
            </div>
        </div>
    </div>

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
                    <p class="fw-bold mb-0">{{ $invoice->invoice_number }}</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" class="d-inline">
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
    <!-- Permanent Delete Modal (Admins only) -->
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
                    <p class="fw-bold mb-0">{{ $invoice->invoice_number }}</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <form action="{{ route('invoices.forceDestroy', $invoice) }}" method="POST" class="d-inline">
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
        document.addEventListener('DOMContentLoaded', function(){
            const deleteModal = document.getElementById('deleteModal');
            if(deleteModal){
                // intercept anular
                const deleteForm = deleteModal.querySelector('form');
                if(deleteForm){
                    deleteForm.addEventListener('submit', function(e){
                        e.preventDefault();
                        const btn = deleteForm.querySelector('button[type="submit"]');
                        if(btn) btn.disabled = true;
                        fetch(deleteForm.action, {
                            credentials: 'same-origin',
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': deleteForm.querySelector('input[name="_token"]').value,
                                'Accept': 'application/json'
                            },
                            body: new FormData(deleteForm)
                        }).then(resp => {
                            if(resp.ok){
                                const bsModal = bootstrap.Modal.getInstance(deleteModal);
                                if(bsModal) bsModal.hide();
                                const container = document.querySelector('.container-fluid.py-4') || document.body;
                                showGlobalToast('Factura anulada', { classname: 'bg-success text-white', delay: 1500 });
                                setTimeout(function(){ window.location.href = "{{ route('invoices.index') }}"; }, 1500);
                            }else{
                                resp.json().then(data => { alert(data?.message || 'Error al anular factura'); if(btn) btn.disabled = false; });
                            }
                        }).catch(()=>{ alert('Error de red.'); if(btn) btn.disabled = false; });
                    });
                }
            }

            // Admin forced delete on single invoice view
            const forceDeleteModal = document.getElementById('forceDeleteModal');
            if(forceDeleteModal){
                const forceForm = forceDeleteModal.querySelector('form');
                if(forceForm){
                    forceForm.addEventListener('submit', function(e){
                        e.preventDefault();
                        const btn = forceForm.querySelector('button[type="submit"]');
                        if(btn) btn.disabled = true;
                        fetch(forceForm.action, {
                            credentials: 'same-origin',
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': forceForm.querySelector('input[name="_token"]').value,
                                'Accept': 'application/json'
                            },
                            body: new FormData(forceForm)
                        }).then(resp => {
                            if(resp.ok){
                                const bsModal = bootstrap.Modal.getInstance(forceDeleteModal);
                                if(bsModal) bsModal.hide();
                                const container = document.querySelector('.container-fluid.py-4') || document.body;
                                showGlobalToast('Factura eliminada correctamente', { classname: 'bg-success text-white', delay: 1500 });
                                setTimeout(function(){ window.location.href = "{{ route('invoices.index') }}"; }, 1500);
                            }else{
                                resp.json().then(data => { alert(data?.message || 'Error al eliminar factura'); if(btn) btn.disabled = false; });
                            }
                        }).catch(()=>{ alert('Error de red.'); if(btn) btn.disabled = false; });
                    });
                }
            }
        });
    </script>
    @endpush

    <!-- Edit Invoice Number Modal -->
    <div class="modal fade" id="editInvoiceNumberModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('invoices.updateNumber', $invoice) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header border-0">
                        <h5 class="modal-title">Editar Número de Factura</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="invoiceNumber" class="form-label">Número de Factura</label>
                            <input type="text" name="invoice_number" id="invoiceNumber" class="form-control" value="{{ $invoice->invoice_number }}" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
