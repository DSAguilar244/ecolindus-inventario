@php
    $activeCashSession = \App\Models\CashSession::where('user_id', auth()->id())
        ->where('status', 'open')
        ->first();
@endphp

<!-- Mobile-first quick actions bar -->
<div class="d-lg-none mb-3">
    <div class="d-grid gap-2">
        @if($activeCashSession)
            <button type="button" class="btn btn-danger btn-lg" data-bs-toggle="modal" data-bs-target="#closeConfirmModal" style="min-height: 44px; font-size: 1.1rem;">
                <i class="bi bi-x-circle me-2"></i>Cerrar Caja
            </button>
            <button type="button" class="btn btn-info btn-lg" data-action="view-cash-summary" style="min-height: 44px; font-size: 1.1rem;">
                <i class="bi bi-eye me-2"></i>Ver Resumen
            </button>
        @else
            <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#openCashModal" style="min-height: 44px; font-size: 1.1rem;">
                <i class="bi bi-play-circle me-2"></i>Abrir Caja
            </button>
        @endif
        <a href="{{ route('cash_sessions.history') }}" class="btn btn-outline-secondary btn-lg" style="min-height: 44px; font-size: 1.1rem;">
            <i class="bi bi-clock-history me-2"></i>Historial
        </a>
    </div>
</div>

<!-- Desktop layout -->
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card bg-white shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title mb-3" id="cashManagementTitle">
                    <i class="bi bi-cash-coin me-2"></i>Gestión de Caja
                </h5>
                
                @if($activeCashSession)
                    <div class="alert alert-success mb-3" role="status" aria-live="polite">
                        <small class="d-block mb-2">✓ Caja activa</small>
                        <small class="text-muted d-block">Apertura: {{ $activeCashSession->opened_at->format('d/m/Y H:i') }}</small>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#closeConfirmModal" style="min-height: 44px;">
                            <i class="bi bi-x-circle me-2"></i>Cerrar Caja
                        </button>
                        <button type="button" class="btn btn-info" data-action="view-cash-summary" style="min-height: 44px;">
                            <i class="bi bi-eye me-2"></i>Ver Resumen
                        </button>
                    </div>
                @else
                    <div class="alert alert-info mb-3" role="status" aria-live="polite">
                        <small>No hay caja abierta</small>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#openCashModal" style="min-height: 44px;">
                            <i class="bi bi-play-circle me-2"></i>Abrir Caja
                        </button>
                    </div>
                @endif
                <hr>
                <a href="{{ route('cash_sessions.history') }}" class="btn btn-outline-secondary w-100" style="min-height: 44px;">
                    <i class="bi bi-clock-history me-2"></i>Historial
                </a>
            </div>
        </div>
    </div>

    @if($activeCashSession)
    <div class="col-lg-8">
        <div class="card bg-white shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title mb-3" id="cashSummaryTitle">
                    <i class="bi bi-receipt me-2"></i>Resumen de Caja
                </h5>
                <div id="cashSummaryContainer" class="text-center">
                    <p class="text-muted" role="status" aria-live="polite">Cargando resumen...</p>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="col-lg-8 d-none d-lg-block">
        <div class="card bg-white shadow-sm h-100">
            <div class="card-body text-center">
                <p class="text-muted mb-3">
                    <i class="bi bi-info-circle me-2"></i>Caja no abierta
                </p>
                <p class="text-secondary small">Abre una caja para ver el resumen de facturas y pagos.</p>
            </div>
        </div>
    </div>
    @endif

<!-- Open Cash Modal -->
<div class="modal fade" id="openCashModal" tabindex="-1" role="dialog" aria-labelledby="openCashModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="openCashModalLabel">Abrir Nueva Sesión de Caja</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="{{ route('cash_sessions.open') }}" method="POST" id="openCashForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="openingAmount" class="form-label fw-bold">Monto Inicial de Caja</label>
                        <input type="number" name="opening_amount" id="openingAmount" class="form-control" step="0.01" min="0" value="0" 
                            required aria-required="true" placeholder="0.00" style="font-size: 1.1rem; min-height: 44px;">
                        <small class="d-block text-muted mt-1">Ingresa el monto disponible en la caja al inicio del turno</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" style="min-height: 44px;">
                        <i class="bi bi-play-circle me-2"></i>Abrir Caja
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Close Cash Modal (Arqueo) -->
<div class="modal fade" id="closeConfirmModal" tabindex="-1" role="dialog" aria-labelledby="closeConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="closeConfirmModalLabel">Arqueo y Cierre de Caja</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="closeConfirmContent">
                <p class="text-muted" role="status" aria-live="polite">Cargando datos...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="confirmCloseBtn" class="btn btn-danger" style="min-height: 44px;">
                    <i class="bi bi-check-circle me-2"></i>Confirmar Cierre
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Summary Modal -->
<div class="modal fade" id="cashSummaryModal" tabindex="-1" role="dialog" aria-labelledby="cashSummaryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cashSummaryModalLabel">Resumen Detallado de Caja</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body table-responsive" id="cashSummaryModalContent" style="max-height: 60vh; overflow-y: auto;">
                <p class="text-muted" role="status" aria-live="polite">Cargando...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" id="exportCashSummaryBtn" class="btn btn-outline-primary">
                    <i class="bi bi-download me-2"></i>Exportar
                </button>
            </div>
        </div>
    </div>
</div>

@if($activeCashSession)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const loadCashSummary = async () => {
        try {
            const response = await fetch('{{ route("cash_sessions.summary") }}', { headers: { 'Accept': 'application/json' } });
            const data = await response.json();
            const container = document.getElementById('cashSummaryContainer');
            
            const t = data.totals || {};
            const html = `
                <div class="row row-cols-1 row-cols-md-2 g-3">
                    <div class="col">
                        <div class="p-3 bg-light rounded">
                            <h6 class="text-muted mb-2">Facturas Emitidas</h6>
                            <h3 data-testid="invoice-count" class="mb-0">${t.invoices_count ?? 0}</h3>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-3 bg-light rounded">
                            <h6 class="text-muted mb-2">Total Facturado</h6>
                            <h3 class="mb-0 text-success" data-testid="total-amount">$${(t.total_invoices ?? 0).toFixed(2)}</h3>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-3 bg-light rounded">
                            <h6 class="text-muted mb-2">En Efectivo</h6>
                            <h4 class="mb-0 text-primary" data-testid="total-cash">$${(t.total_cash ?? 0).toFixed(2)}</h4>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-3 bg-light rounded">
                            <h6 class="text-muted mb-2">En Transferencia</h6>
                            <h4 class="mb-0 text-info" data-testid="total-transfer">$${(t.total_transfer ?? 0).toFixed(2)}</h4>
                        </div>
                    </div>
                </div>
            `;
            container.innerHTML = html;
            container.setAttribute('role', 'region');
            container.setAttribute('aria-live', 'polite');
        } catch (error) {
            const container = document.getElementById('cashSummaryContainer');
            container.innerHTML = '<p class="text-danger">Error al cargar el resumen</p>';
        }
    };

    // Load summary on page load and refresh every 30 seconds
    loadCashSummary();
    setInterval(loadCashSummary, 30000);

    // View Summary Button (both mobile and desktop)
    const setupViewSummaryButton = () => {
        const viewBtns = document.querySelectorAll('[data-action="view-cash-summary"]');
        viewBtns.forEach(viewBtn => {
            viewBtn.addEventListener('click', async (e) => {
                e.preventDefault();
                console.log('View Summary button clicked');
                try {
                    const response = await fetch('{{ route("cash_sessions.summary") }}', { headers: { 'Accept': 'application/json' } });
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    const data = await response.json();
                    console.log('Data received:', data);
                    const s = data.session || {};
                    const t = data.totals || {};
                    const invoices = data.invoices || [];

                let tableHtml = '';
                if (invoices.length > 0) {
                    tableHtml = `
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Factura</th>
                                    <th class="text-end">Subtotal</th>
                                    <th class="text-end">IVA</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Efectivo</th>
                                    <th class="text-end">Transf.</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${invoices.map(inv => `
                                    <tr>
                                        <td>${inv.invoice_number ?? 'N/A'}</td>
                                        <td class="text-end">$${(inv.subtotal ?? 0).toFixed(2)}</td>
                                        <td class="text-end">$${(inv.tax ?? 0).toFixed(2)}</td>
                                        <td class="text-end fw-bold">$${(inv.total ?? 0).toFixed(2)}</td>
                                        <td class="text-end">$${(inv.payment?.cash ?? 0).toFixed(2)}</td>
                                        <td class="text-end">$${(inv.payment?.transfer ?? 0).toFixed(2)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    `;
                }

                const html = `
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Resumen de Facturas</h6>
                        <div class="row row-cols-2">
                            <div class="col"><small class="text-muted">Subtotal:</small></div>
                            <div class="col text-end"><small class="fw-bold">$${(t.subtotal ?? 0).toFixed(2)}</small></div>
                            <div class="col"><small class="text-muted">IVA:</small></div>
                            <div class="col text-end"><small class="fw-bold">$${(t.tax ?? 0).toFixed(2)}</small></div>
                            <div class="col"><small class="text-muted">Total Facturado:</small></div>
                            <div class="col text-end"><small class="fw-bold text-success">$${(t.total_invoices ?? 0).toFixed(2)}</small></div>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Arqueo de Caja</h6>
                        <div class="alert alert-light border">
                            <div class="row row-cols-2 small">
                                <div class="col"><strong>Monto Inicial:</strong></div>
                                <div class="col text-end"><strong>$${(s.opening_amount ?? 0).toFixed(2)}</strong></div>
                                <div class="col"><small class="text-muted">+ Efectivo:</small></div>
                                <div class="col text-end"><small class="text-muted">$${(t.total_cash ?? 0).toFixed(2)}</small></div>
                                <div class="col"><small class="text-muted">+ Transferencia:</small></div>
                                <div class="col text-end"><small class="text-muted">$${(t.total_transfer ?? 0).toFixed(2)}</small></div>
                                <div class="col"><strong class="text-primary">= Monto Esperado:</strong></div>
                                <div class="col text-end"><strong class="text-primary">$${(t.expected_closing ?? 0).toFixed(2)}</strong></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Detalles de Pagos</h6>
                        <div class="row row-cols-2">
                            <div class="col"><small class="text-muted">En Efectivo:</small></div>
                            <div class="col text-end"><small class="fw-bold text-primary">$${(t.total_cash ?? 0).toFixed(2)}</small></div>
                            <div class="col"><small class="text-muted">En Transferencia:</small></div>
                            <div class="col text-end"><small class="fw-bold text-info">$${(t.total_transfer ?? 0).toFixed(2)}</small></div>
                        </div>
                    </div>

                    <hr>
                    ${tableHtml}
                `;
                document.getElementById('cashSummaryModalContent').innerHTML = html;
                const modal = new bootstrap.Modal(document.getElementById('cashSummaryModal'));
                modal.show();
            } catch (error) {
                console.error('Error loading cash summary:', error);
                document.getElementById('cashSummaryModalContent').innerHTML = '<p class="text-danger">Error al cargar el resumen detallado: ' + error.message + '</p>';
                const modal = new bootstrap.Modal(document.getElementById('cashSummaryModal'));
                modal.show();
            }
            });
        });
    };
    setupViewSummaryButton();

    // Close Cash Flow
    document.getElementById('closeConfirmModal')?.addEventListener('show.bs.modal', async () => {
        try {
            const response = await fetch('{{ route("cash_sessions.summary") }}', { headers: { 'Accept': 'application/json' } });
            const data = await response.json();
            const t = data.totals || {};
            const s = data.session || {};
            
            const html = `
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="p-2 bg-light rounded">
                            <small class="text-muted d-block">Monto Inicial</small>
                            <h5 class="mb-0">$${(s.opening_amount ?? 0).toFixed(2)}</h5>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-2 bg-light rounded">
                            <small class="text-muted d-block">Total Facturado</small>
                            <h5 class="mb-0 text-success">$${(t.total_invoices ?? 0).toFixed(2)}</h5>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-2 bg-light rounded">
                            <small class="text-muted d-block">En Efectivo</small>
                            <h5 class="mb-0 text-primary">$${(t.total_cash ?? 0).toFixed(2)}</h5>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-2 bg-light rounded">
                            <small class="text-muted d-block">En Transferencia</small>
                            <h5 class="mb-0 text-info">$${(t.total_transfer ?? 0).toFixed(2)}</h5>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info" role="status" aria-live="polite">
                    <small><strong>Monto Esperado en Caja:</strong> $${(t.expected_closing ?? 0).toFixed(2)}</small>
                </div>

                <div class="mb-3">
                    <label for="reportedClosingAmount" class="form-label fw-bold">Monto Encontrado en Caja (Arqueo)</label>
                    <input type="number" id="reportedClosingAmount" class="form-control" step="0.01" 
                        value="${(t.expected_closing ?? 0).toFixed(2)}" required 
                        aria-required="true" placeholder="0.00" style="font-size: 1.1rem; min-height: 44px;">
                </div>

                <div class="alert mb-3" id="differenceDisplay" role="status" aria-live="polite">
                    <small><strong>Diferencia:</strong> <span id="differenceValue" class="h5">$0.00</span></small>
                </div>

                <div id="closeNoteContainer" style="display:none;">
                    <div class="alert alert-warning mb-3" role="status">
                        <small>Hay una diferencia. Por favor proporciona una explicación.</small>
                    </div>
                    <div class="mb-3">
                        <label for="closeNote" class="form-label fw-bold">Nota Explicativa *</label>
                        <textarea id="closeNote" class="form-control" rows="3" 
                            placeholder="Ej: Diferencia por cambio no registrado..." 
                            style="min-height: 80px;" aria-required="true"></textarea>
                    </div>
                </div>
            `;
            document.getElementById('closeConfirmContent').innerHTML = html;

            // Update difference on input change - NEW: bind to this newly created content
            const reportedInput = document.getElementById('reportedClosingAmount');
            const expected = t.expected_closing ?? 0;
            const noteContainer = document.getElementById('closeNoteContainer');
            const differenceValue = document.getElementById('differenceValue');
            const differenceDisplay = document.getElementById('differenceDisplay');
            const closeNote = document.getElementById('closeNote');

            const updateDifference = () => {
                const reported = parseFloat(reportedInput.value) || 0;
                const diff = expected - reported;
                
                differenceValue.textContent = '$' + diff.toFixed(2);
                
                if (Math.abs(diff) < 0.01) {
                    differenceDisplay.className = 'alert alert-success mb-3';
                    differenceValue.className = 'h5 text-success';
                    noteContainer.style.display = 'none';
                    closeNote.removeAttribute('required');
                } else if (diff > 0) {
                    differenceDisplay.className = 'alert alert-warning mb-3';
                    differenceValue.className = 'h5 text-warning';
                    noteContainer.style.display = 'block';
                    closeNote.setAttribute('required', 'required');
                } else {
                    differenceDisplay.className = 'alert alert-danger mb-3';
                    differenceValue.className = 'h5 text-danger';
                    noteContainer.style.display = 'block';
                    closeNote.setAttribute('required', 'required');
                }
            };

            reportedInput.addEventListener('input', updateDifference);
            updateDifference();
            reportedInput.focus();
        } catch (error) {
            document.getElementById('closeConfirmContent').innerHTML = '<p class="text-danger">Error al cargar datos de cierre</p>';
        }
    });

    // Confirm Close Button
    (function(){
        const confirmBtn = document.getElementById('confirmCloseBtn');
        const reportedInputSelector = '#reportedClosingAmount';
        const noteInputSelector = '#closeNote';
        const noteContainer = document.getElementById('closeNoteContainer');

        const getReportedValue = () => {
            const el = document.querySelector(reportedInputSelector);
            if (!el) return NaN;
            // Normalize comma to dot and trim
            const raw = String(el.value || '').trim().replace(',', '.');
            const n = parseFloat(raw);
            return Number.isFinite(n) ? n : NaN;
        };

        const updateConfirmState = () => {
            const reported = getReportedValue();
            const expected = parseFloat(document.getElementById('differenceValue')?.dataset?.expected ?? 0) || 0;
            // compute current diff: expected - reported
            // we can read displayed diff; however rely on noteContainer visibility
            if (noteContainer && noteContainer.style.display !== 'none') {
                const note = document.querySelector(noteInputSelector)?.value || '';
                confirmBtn.disabled = !note.trim();
            } else {
                confirmBtn.disabled = false;
            }
        };

        // Wire input listeners to keep button state in sync
        document.addEventListener('input', (e) => {
            if (e.target && (e.target.matches(reportedInputSelector) || e.target.matches(noteInputSelector))) {
                updateConfirmState();
            }
        });

        confirmBtn?.addEventListener('click', async () => {
            const reportedInput = document.querySelector(reportedInputSelector);
            const noteInput = document.querySelector(noteInputSelector);

            if (!reportedInput) {
                const alertHtml = `<div class="alert alert-danger" role="alert">Error: No se encontró el campo de monto reportado</div>`;
                document.getElementById('closeConfirmContent').insertAdjacentHTML('afterbegin', alertHtml);
                return;
            }

            // Normalize and validate reported amount
            const raw = String(reportedInput.value || '').trim().replace(',', '.');
            if (raw === '') {
                const alertHtml = `<div class="alert alert-danger" role="alert">Por favor ingresa el monto encontrado en caja.</div>`;
                document.getElementById('closeConfirmContent').insertAdjacentHTML('afterbegin', alertHtml);
                reportedInput.focus();
                return;
            }

            const reported = parseFloat(raw);
            if (!Number.isFinite(reported)) {
                const alertHtml = `<div class="alert alert-danger" role="alert">Formato de monto inválido.</div>`;
                document.getElementById('closeConfirmContent').insertAdjacentHTML('afterbegin', alertHtml);
                reportedInput.focus();
                return;
            }

            // If note is required, ensure present
            if (noteContainer && noteContainer.style.display !== 'none') {
                if (!noteInput || !noteInput.value.trim()) {
                    const alertHtml = `<div class="alert alert-danger" role="alert">Por favor proporciona una nota explicativa para la diferencia</div>`;
                    document.getElementById('closeConfirmContent').insertAdjacentHTML('afterbegin', alertHtml);
                    noteInput?.focus();
                    return;
                }
            }

            // Disable to avoid double clicks
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = 'Procesando...';

            try {
                // Try multiple ways to get the CSRF token for robustness
                let token = document.querySelector('meta[name="csrf-token"]')?.content;
                if (!token) {
                    token = document.querySelector('input[name="_token"]')?.value;
                }
                if (!token) {
                    showGlobalToast('Token CSRF no disponible. Por favor recarga la página.', {type: 'error'});
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Confirmar Cierre';
                    return;
                }
                const idempotencyKey = 'cs_close_' + (Date.now()) + '_' + Math.random().toString(36).slice(2,9);

                const response = await fetch('{{ route("cash_sessions.close") }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({
                        reported_closing_amount: reported,
                        notes: noteInput?.value || '',
                        idempotency_key: idempotencyKey
                    })
                });

                const container = document.getElementById('closeConfirmContent');
                // remove existing alerts
                container.querySelectorAll('.alert').forEach(a => a.remove());

                if (response.status === 422) {
                    const error = await response.json();
                    const alertHtml = `<div class="alert alert-danger" role="alert">${error.message || 'Error de validación'}</div>`;
                    container.insertAdjacentHTML('afterbegin', alertHtml);
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Confirmar Cierre';
                    return;
                }

                if (!response.ok) {
                    const error = await response.json().catch(() => ({}));
                    const alertHtml = `<div class="alert alert-danger" role="alert">${error.message || 'Error al cerrar la caja'}</div>`;
                    container.insertAdjacentHTML('afterbegin', alertHtml);
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Confirmar Cierre';
                    return;
                }

                const result = await response.json();

                // Hide modal and show success feedback near the cash section
                const modalEl = document.getElementById('closeConfirmModal');
                bootstrap.Modal.getInstance(modalEl)?.hide();

                // Create a transient success alert in cashSummaryContainer
                const summaryContainer = document.getElementById('cashSummaryContainer');
                if (summaryContainer) {
                    const success = document.createElement('div');
                    success.className = 'alert alert-success';
                    success.role = 'status';
                    success.textContent = result.message || 'Caja cerrada correctamente';
                    summaryContainer.prepend(success);
                    setTimeout(() => success.remove(), 3000);
                }

                // Try to refresh history table fragment if present without full reload
                try {
                    const historyTable = document.getElementById('cashHistoryTable');
                    if (historyTable) {
                        const htmlResp = await fetch('{{ route("cash_sessions.history") }}');
                        if (htmlResp.ok) {
                            const htmlText = await htmlResp.text();
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(htmlText, 'text/html');
                            const newTable = doc.getElementById('cashHistoryTable');
                            if (newTable) {
                                historyTable.replaceWith(newTable);
                            } else {
                                // fallback: reload if fragment not found
                                location.reload();
                            }
                        } else {
                            location.reload();
                        }
                    } else {
                        // If there's no history table on this page, reload to reflect closed session elsewhere
                        location.reload();
                    }
                } catch (e) {
                    location.reload();
                }

            } catch (error) {
                const container = document.getElementById('closeConfirmContent');
                const alertHtml = `<div class="alert alert-danger" role="alert">Error al procesar el cierre</div>`;
                container.insertAdjacentHTML('afterbegin', alertHtml);
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Confirmar Cierre';
            }
        });
    })();

    // Export Summary
    document.getElementById('exportCashSummaryBtn')?.addEventListener('click', async () => {
        try {
            const response = await fetch('{{ route("cash_sessions.exportPdf") }}');
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'caja_resumen.pdf';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        } catch (error) {
            console.error('Error al exportar PDF:', error);
            showGlobalToast('Error al exportar el resumen de caja. Por favor intenta nuevamente.', {type: 'error'});
        }
    });
});
</script>
@endif