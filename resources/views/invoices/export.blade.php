<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Exportar Facturas</title>
    <style>
        body{font-family: DejaVu Sans, sans-serif; font-size:12px}
        table{width:100%;border-collapse:collapse}
        td,th{border:1px solid #ddd;padding:6px}
        thead th{background:#f5f5f5}
        .right{text-align:right}
    </style>
</head>
<body>
    <h3>Listado de Facturas</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Dirección</th>
                <th>Fecha</th>
                <th class="right">Subtotal</th>
                <th class="right">Impuesto</th>
                <th class="right">Total</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoices as $inv)
            <tr>
                <td>{{ $inv->invoice_number }}</td>
                <td>{{ optional($inv->customer)->first_name }} {{ optional($inv->customer)->last_name }} - {{ optional($inv->customer)->identification }}</td>
                <td>{{ optional($inv->customer)->address }}</td>
                <td>{{ $inv->date->format('Y-m-d') }}</td>
                <td class="right">{{ number_format($inv->subtotal ?? 0,2) }}</td>
                <td class="right">{{ number_format($inv->tax_total ?? 0,2) }}</td>
                <td class="right">{{ number_format($inv->total ?? 0,2) }}</td>
                <td>
                    @if($inv->status === \App\Models\Invoice::STATUS_ANULADA)
                        Anulada
                    @elseif($inv->status === \App\Models\Invoice::STATUS_EMITIDA)
                        Emitida
                    @else
                        {{ ucfirst($inv->status) }}
                    @endif
                </td>
            </tr>
            @endforeach
+        </tbody>
+    </table>
+</body>
+</html>
