<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        @page { size: A4; margin: 12mm 12mm; }
        * { margin: 0; padding: 0; }
        body { 
            font-family: 'DejaVu Sans', sans-serif; 
            font-size: 11px; 
            line-height: 1.4; 
            color: #222; 
        }
        table { width: 100%; border-collapse: collapse; }
        th { 
            background: #f0f0f0; 
            padding: 6px; 
            border: 1px solid #999; 
            text-align: left;
            font-size: 10px; 
            font-weight: bold; 
        }
        td { padding: 4px; }
        .border-cell { border: 1px solid #ccc; }
        .right { text-align: right; }
        .header-table { border: none; margin-bottom: 12px; }
        .header-table td { border: none; padding: 0; }
        .invoice-box {
            border: 2px solid #333;
            background: #f9f9f9;
            padding: 10px;
            display: inline-block;
        }
        .invoice-number {
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 2px;
        }
        .section-title {
            font-size: 10px;
            font-weight: bold;
            color: #333;
            margin-top: 8px;
            margin-bottom: 4px;
        }
        .items-table th { font-size: 10px; }
        .items-table td { font-size: 10px; }
        .totals-table { width: 40%; margin-left: auto; margin-top: 12px; }
        .totals-table tr td { border: none; padding: 4px 6px; }
        .totals-label { font-size: 10px; }
        .totals-value { text-align: right; font-weight: bold; font-size: 10px; }
        .total-row td { border-top: 2px solid #333; padding-top: 6px; padding-bottom: 6px; }
        .total-row .totals-label { font-size: 11px; font-weight: bold; }
        .total-row .totals-value { font-size: 12px; font-weight: bold; }
        .payment-detail { font-size: 9px; color: #666; }
        hr { border: none; border-top: 1px solid #ddd; margin: 8px 0; }
        .footer { text-align: center; font-size: 9px; color: #999; margin-top: 20px; }
    </style>
</head>
<body>
    <!-- HEADER: Company Info + Invoice Number -->
    <table class="header-table">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                @php $logoPath = public_path('images/ecolindus-logo.png'); @endphp
                @if(file_exists($logoPath))
                    <img src="file://{{ $logoPath }}" alt="Logo" style="max-width: 100px; height: auto; margin-bottom: 4px;">
                @else
                    <div style="font-size: 13px; font-weight: bold; margin-bottom: 4px;">{{ config('company.name', 'ECOLINDUS') }}</div>
                @endif
                <div style="font-size: 10px;">
                    <strong>RUC:</strong> {{ config('company.ruc') }}
                </div>
            </td>
            <td style="width: 50%; vertical-align: top; text-align: right;">
                @php
                    $est = config('company.establishment_number') ?? '';
                    $emi = config('company.emission_number') ?? '';
                    $seq = null;
                    if (preg_match('/(\d+)$/', $invoice->invoice_number ?? '', $m)) {
                        $seq = $m[1];
                    }
                    if (empty($seq)) {
                        $seq = $invoice->id ?? '0';
                    }
                    $seqPadded = str_pad($seq, 10, '0', STR_PAD_LEFT);
                    $displayNumber = trim("{$est}-{$emi}-{$seqPadded}", '-');
                @endphp
                <div class="invoice-box">
                    <div style="font-size: 11px; font-weight: bold;">FACTURA</div>
                    <div class="invoice-number">{{ $displayNumber }}</div>
                    <div style="font-size: 9px; color: #666; margin-top: 4px;">{{ optional($invoice->date)->format('Y-m-d') ?? optional($invoice->created_at)->format('Y-m-d') ?? '' }}</div>
                </div>
            </td>
        </tr>
    </table>

    <hr>

    <!-- CLIENT INFO -->
    <div class="section-title">CLIENTE</div>
    <table class="header-table">
        <tr>
            <td style="width: 70%;">
                <div style="font-size: 11px;">
                    <strong>{{ optional($invoice->customer)->first_name }} {{ optional($invoice->customer)->last_name }}</strong>
                </div>
                @if(optional($invoice->customer)->identification)
                    <div style="font-size: 10px; color: #666;">Cédula/RUC: {{ optional($invoice->customer)->identification }}</div>
                @endif
                @if(optional($invoice->customer)->address)
                    <div style="font-size: 10px; color: #666;">Dirección: {{ optional($invoice->customer)->address }}</div>
                @endif
            </td>
            <td style="width: 30%; vertical-align: top;">
                @if($invoice->payment_method)
                    <div style="font-size: 10px; font-weight: bold; color: #333;">FORMA DE PAGO</div>
                    <div style="font-size: 11px;">{{ $invoice->payment_method }}</div>
                @endif
            </td>
        </tr>
    </table>

    <hr>

    <!-- ITEMS TABLE -->
    <div class="section-title">DETALLE DE ARTÍCULOS</div>
    <table class="items-table">
        <tr>
            <th style="width: 45%;">Producto</th>
            <th style="width: 12%;" class="right">Cantidad</th>
            <th style="width: 15%;" class="right">Precio</th>
            <th style="width: 12%;" class="right">Impuesto</th>
            <th style="width: 16%;" class="right">Total</th>
        </tr>
        @foreach($invoice->items as $item)
        <tr>
            <td class="border-cell">{{ optional($item->product)->name }}</td>
            <td class="border-cell right">{{ $item->quantity }}</td>
            @php
                $taxRate = $item->tax_rate ?? 0;
                $displayUnit = ($taxRate > 0) ? ($item->unit_price * (1 + ($taxRate/100))) : $item->unit_price;
                $lineTax = ($taxRate/100) * $item->line_total;
            @endphp
            <td class="border-cell right">${{ number_format($displayUnit, 2) }}</td>
            <td class="border-cell right">{{ $item->tax_rate }}%</td>
            <td class="border-cell right"><strong>${{ number_format($item->line_total + $lineTax, 2) }}</strong></td>
        </tr>
        @endforeach
    </table>

    <!-- TOTALS TABLE -->
    <table class="totals-table">
        <tr>
            <td class="totals-label">Subtotal:</td>
            <td class="totals-value">${{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td class="totals-label">IVA:</td>
            <td class="totals-value">${{ number_format($invoice->tax_total, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td class="totals-label">TOTAL A PAGAR:</td>
            <td class="totals-value">${{ number_format($invoice->total, 2) }}</td>
        </tr>
    </table>

    @if($invoice->payment)
    <table class="totals-table" style="margin-top: 8px;">
        <tr>
            <td class="payment-detail">Efectivo:</td>
            <td class="payment-detail right">${{ number_format($invoice->payment->cash_amount, 2) }}</td>
        </tr>
        <tr>
            <td class="payment-detail">Transferencia:</td>
            <td class="payment-detail right">${{ number_format($invoice->payment->transfer_amount, 2) }}</td>
        </tr>
    </table>
    @endif

    <div class="footer">
        <div>Documento generado por ECOLINDUS {{ now()->format('Y-m-d H:i') }}</div>
    </div>
</body>
</html>
