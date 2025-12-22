<table>
    <thead>
        <tr>
            <th colspan="6" style="font-size: 14px; font-weight: bold;">REPORTE MENSUAL</th>
        </tr>
        <tr>
            <th colspan="6">Periodo: {{ $dateFrom }} — {{ $dateTo }}</th>
        </tr>
        <tr>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <tr style="font-weight: bold;">
            <td>Total Ventas</td>
            <td>${{ number_format($totalSales, 2) }}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr style="font-weight: bold;">
            <td>IVA Recaudado</td>
            <td>${{ number_format($totalIva, 2) }}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
        </tr>
        <tr style="font-weight: bold; background-color: #f0f0f0;">
            <td>Efectivo</td>
            <td>${{ number_format($byCash, 2) }}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr style="font-weight: bold; background-color: #f0f0f0;">
            <td>Transferencia</td>
            <td>${{ number_format($byTransfer, 2) }}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
        </tr>
        <tr style="font-weight: bold;">
            <td>Facturas Emitidas</td>
            <td>{{ $countEmitted }}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr style="font-weight: bold;">
            <td>Facturas Pendientes</td>
            <td>{{ $countPending }}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
        </tr>
        <tr>
            <td></td>
        </tr>
        <tr style="font-weight: bold;">
            <td>#</td>
            <td>Cliente</td>
            <td>Subtotal</td>
            <td>IVA</td>
            <td>Total</td>
            <td>Estado</td>
        </tr>
        @foreach($invoices as $invoice)
        <tr>
            <td>{{ $invoice->invoice_number }}</td>
            <td>{{ $invoice->customer?->first_name . ' ' . $invoice->customer?->last_name }}</td>
            <td>${{ number_format($invoice->subtotal, 2) }}</td>
            <td>${{ number_format($invoice->tax_total, 2) }}</td>
            <td>${{ number_format($invoice->total, 2) }}</td>
            <td>{{ ucfirst($invoice->status) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
