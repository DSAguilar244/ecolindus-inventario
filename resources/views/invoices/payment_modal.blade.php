@php
    $totalInvoice = $invoice->total;
    $payment = $invoice->payment;
    $cashAmount = $payment ? $payment->cash_amount : 0;
    $transferAmount = $payment ? $payment->transfer_amount : 0;
@endphp

<div id="paymentModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('invoice_payments.store', $invoice) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Pago</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Total de Factura</label>
                        <input type="text" class="form-control" value="{{ number_format($totalInvoice, 2) }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label for="cashAmount" class="form-label">Monto en Efectivo</label>
                        <input type="number" name="cash_amount" id="cashAmount" class="form-control" step="0.01" min="0" value="{{ $cashAmount }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="transferAmount" class="form-label">Monto en Transferencia</label>
                        <input type="number" name="transfer_amount" id="transferAmount" class="form-control" step="0.01" min="0" value="{{ $transferAmount }}" required>
                    </div>
                    <div class="alert alert-info" id="paymentValidation">
                        <small>Total ingresado: <strong id="totalInput">0.00</strong></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Pago</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cashInput = document.getElementById('cashAmount');
        const transferInput = document.getElementById('transferAmount');
        const totalDisplay = document.getElementById('totalInput');
        const totalInvoice = {{ $totalInvoice }};
        const paymentValidation = document.getElementById('paymentValidation');

        function updateTotal() {
            const cash = parseFloat(cashInput.value) || 0;
            const transfer = parseFloat(transferInput.value) || 0;
            const total = cash + transfer;
            totalDisplay.textContent = total.toFixed(2);

            if (Math.abs(total - totalInvoice) < 0.01) {
                paymentValidation.classList.remove('alert-warning');
                paymentValidation.classList.add('alert-success');
            } else if (total > totalInvoice) {
                paymentValidation.classList.remove('alert-success');
                paymentValidation.classList.add('alert-warning');
            } else {
                paymentValidation.classList.remove('alert-success');
                paymentValidation.classList.add('alert-warning');
            }
        }

        cashInput.addEventListener('input', updateTotal);
        transferInput.addEventListener('input', updateTotal);
        updateTotal();
    });
</script>
