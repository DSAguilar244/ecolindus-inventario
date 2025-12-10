@php
    $activeCashSession = \App\Models\CashSession::where('user_id', auth()->id())
        ->where('status', 'open')
        ->first();
@endphp

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card bg-white shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title mb-3">
                    <i class="bi bi-cash-coin me-2"></i>Gestión de Caja
                </h5>
                
                @if($activeCashSession)
                    <div class="alert alert-success mb-3">
                        <small>Caja abierta desde:<br>{{ $activeCashSession->opened_at->format('Y-m-d H:i') }}</small>
                    </div>
                    
                    <form action="{{ route('cash_sessions.close') }}" method="POST" id="closeCashForm">
                        @csrf
                        <div class="mb-3">
                            <label for="closingAmount" class="form-label">Monto en Caja (Cierre)</label>
                            <input type="number" name="closing_amount" id="closingAmount" class="form-control" step="0.01" min="0" required>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-x-circle me-2"></i>Cerrar Caja
                        </button>
                    </form>
                    
                    <button type="button" class="btn btn-info btn-sm w-100 mt-2" id="viewCashSummary">
                        <i class="bi bi-eye me-1"></i>Ver Resumen
                    </button>
                @else
                    <form action="{{ route('cash_sessions.open') }}" method="POST" id="openCashForm">
                        @csrf
                        <div class="mb-3">
                            <label for="openingAmount" class="form-label">Monto Inicial</label>
                            <input type="number" name="opening_amount" id="openingAmount" class="form-control" step="0.01" min="0" value="0" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-play-circle me-2"></i>Abrir Caja
                        </button>
                    </form>
                @endif
                <hr>
                <a href="{{ route('cash_sessions.history') }}" class="btn btn-outline-secondary w-100 btn-sm">
                    <i class="bi bi-clock-history me-1"></i>Historial de Cajas
                </a>
            </div>
        </div>
    </div>

    @if($activeCashSession)
    <div class="col-lg-8">
        <div class="card bg-white shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title mb-3">
                    <i class="bi bi-receipt me-2"></i>Resumen de Caja
                </h5>
                <div id="cashSummaryContainer" class="text-center">
                    <p class="text-muted">Cargando resumen...</p>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="col-lg-8">
        <div class="card bg-white shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title mb-3">
                    <i class="bi bi-receipt me-2"></i>Resumen de Caja
                </h5>
                <div class="text-center">
                    <p class="text-muted mb-3">
                        <i class="bi bi-info-circle"></i> Caja no abierta
                    </p>
                    <p class="text-secondary small">Abre una caja para ver el resumen de facturas y pagos.</p>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Close Confirmation Modal -->
    <div class="modal fade" id="closeConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Cierre de Caja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="closeConfirmContent">Cargando...</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="confirmCloseBtn" class="btn btn-danger">Confirmar Cierre</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Modal -->
    <div class="modal fade" id="cashSummaryModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Resumen de Caja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="cashSummaryModalContent">Cargando...</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" id="exportCashSummaryBtn" class="btn btn-outline-primary">Exportar / Imprimir</button>
                </div>
            </div>
        </div>
    </div>
</div>

@if($activeCashSession)
<script>
    function loadCashSummary() {
        fetch('{{ route("cash_sessions.summary") }}')
            .then(response => response.json())
            .then(data => {
                const html = `
                    <div class="row text-center">
                        <div class="col-md-6">
                            <div class="p-3">
                                <h6 class="text-muted">Facturas Emitidas</h6>
                                <h3 data-testid="invoice-count">${data.totals?.invoices_count ?? 0}</h3>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3">
                                <h6 class="text-muted">Total</h6>
                                <h3 data-testid="total-amount">$${parseFloat(data.totals.total_invoices).toFixed(2)}</h3>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <h6 class="text-muted">En Efectivo</h6>
                                <h4 class="text-success" data-testid="total-cash">$${parseFloat(data.totals.total_cash).toFixed(2)}</h4>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <h6 class="text-muted">En Transferencia</h6>
                                <h4 class="text-info" data-testid="total-transfer">$${parseFloat(data.totals.total_transfer).toFixed(2)}</h4>
                            </div>
                        </div>
                    </div>
                `;
                document.getElementById('cashSummaryContainer').innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('cashSummaryContainer').innerHTML = '<p class="text-danger">Error al cargar el resumen</p>';
            });
    }

    document.addEventListener('DOMContentLoaded', function() {
        loadCashSummary();
        const viewBtn = document.getElementById('viewCashSummary');
        if (viewBtn) {
            viewBtn.addEventListener('click', loadCashSummary);
        }
        // Refresh every 30 seconds
        setInterval(loadCashSummary, 30000);
    });
    
    // Open detailed summary modal
    document.addEventListener('click', function(e){
        if(e.target && e.target.id === 'viewCashSummary'){
            e.preventDefault();
            fetch('{{ route("cash_sessions.summary") }}')
                .then(r=>r.json())
                .then(data=>{
                    const s = data.session;
                    const t = data.totals;
                    let html = `
                        <div class="row mb-3">
                            <div class="col-md-6"><strong>Usuario ID:</strong> ${s.user_id}</div>
                            <div class="col-md-6"><strong>Apertura:</strong> ${s.opened_at}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Facturas:</strong> ${t.invoices_count}</div>
                            <div class="col-md-3"><strong>Subtotal:</strong> $${t.subtotal.toFixed(2)}</div>
                            <div class="col-md-3"><strong>IVA:</strong> $${t.tax.toFixed(2)}</div>
                            <div class="col-md-3"><strong>Total:</strong> $${t.total_invoices.toFixed(2)}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6"><strong>Efectivo:</strong> $${t.total_cash.toFixed(2)}</div>
                            <div class="col-md-6"><strong>Transferencia:</strong> $${t.total_transfer.toFixed(2)}</div>
                        </div>
                        <hr />
                        <h6>Listado de Facturas</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead><tr><th>#</th><th>Cliente</th><th>Subtotal</th><th>IVA</th><th>Total</th><th>Efectivo</th><th>Transferencia</th></tr></thead>
                                <tbody>
                    `;
                    data.invoices.forEach(function(inv){
                        html += `<tr>
                            <td>${inv.invoice_number}</td>
                            <td>${inv.customer}</td>
                            <td>$${parseFloat(inv.subtotal).toFixed(2)}</td>
                            <td>$${parseFloat(inv.tax).toFixed(2)}</td>
                            <td>$${parseFloat(inv.total).toFixed(2)}</td>
                            <td>$${parseFloat(inv.payment.cash).toFixed(2)}</td>
                            <td>$${parseFloat(inv.payment.transfer).toFixed(2)}</td>
                        </tr>`;
                    });
                    html += `</tbody></table></div>`;
                    document.getElementById('cashSummaryModalContent').innerHTML = html;
                    var modal = new bootstrap.Modal(document.getElementById('cashSummaryModal'));
                    modal.show();
                })
                .catch(err=>{ console.error(err); alert('Error cargando resumen'); });
        }
    });

    // Close confirmation flow
    document.addEventListener('submit', function(e){
        if(e.target && e.target.id === 'closeCashForm'){
            e.preventDefault();
            // Load summary and show confirm modal
            fetch('{{ route("cash_sessions.summary") }}')
                .then(r=>r.json())
                .then(data=>{
                    const t = data.totals;
                    const session = data.session;
                    let html = `<div class="row">
                        <div class="col-md-6"><strong>Monto Inicial:</strong> $${parseFloat(session.opening_amount).toFixed(2)}</div>
                        <div class="col-md-6"><strong>Total Facturado:</strong> $${parseFloat(t.total_invoices).toFixed(2)}</div>
                        <div class="col-md-6"><strong>Total Efectivo:</strong> $${parseFloat(t.total_cash).toFixed(2)}</div>
                        <div class="col-md-6"><strong>Total Transferencia:</strong> $${parseFloat(t.total_transfer).toFixed(2)}</div>
                        <div class="col-md-12"><strong>Monto Esperado en Caja:</strong> $${parseFloat(t.expected_closing).toFixed(2)}</div>
                    </div>`;
                    document.getElementById('closeConfirmContent').innerHTML = html;
                    var modal = new bootstrap.Modal(document.getElementById('closeConfirmModal'));
                    modal.show();
                })
                .catch(err=>{ console.error(err); alert('Error calculando cierre'); });
        }
    });

    // When user confirms closure, submit original form with reported closing amount for audit
    document.getElementById('confirmCloseBtn')?.addEventListener('click', function(){
        // get expected closing from summary
        fetch('{{ route("cash_sessions.summary") }}')
            .then(r=>r.json())
            .then(data=>{
                const expected = data.totals.expected_closing;
                // append hidden reported_closing_amount to form
                const form = document.getElementById('closeCashForm');
                if(!form) return alert('Formulario no encontrado');
                if(!form.querySelector('input[name="reported_closing_amount"]')){
                    const inp = document.createElement('input');
                    inp.type = 'hidden'; inp.name = 'reported_closing_amount'; inp.value = expected;
                    form.appendChild(inp);
                } else { form.querySelector('input[name="reported_closing_amount"]').value = expected; }
                form.submit();
            });
    });

    // Export/print summary
    document.getElementById('exportCashSummaryBtn')?.addEventListener('click', function(){
        // Simple print of modal contents
        const content = document.getElementById('cashSummaryModalContent').innerHTML;
        const w = window.open('', '_blank');
        w.document.write('<html><head><title>Resumen de Caja</title></head><body>' + content + '</body></html>');
        w.document.close();
        w.print();
    });
</script>
@endif
