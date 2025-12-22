<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte Mensual - ECOLINDUS</title>
    <style>
        @page { size: A4; margin: 12mm 12mm; }
        * { margin: 0; padding: 0; }
        body { 
            font-family: 'DejaVu Sans', sans-serif; 
            font-size: 11px; 
            line-height: 1.5; 
            color: #222; 
        }
        .header { 
            text-align: center; 
            border-bottom: 2px solid #333; 
            padding-bottom: 12px; 
            margin-bottom: 24px; 
        }
        .header-title { 
            font-size: 20px; 
            font-weight: bold; 
            margin-bottom: 4px; 
        }
        .header-subtitle { 
            font-size: 12px; 
            color: #666; 
            margin-bottom: 4px; 
        }
        .header-info { 
            font-size: 10px; 
            color: #999; 
        }
        .section { 
            margin-bottom: 24px; 
            page-break-inside: avoid; 
        }
        .section-title { 
            font-size: 12px; 
            font-weight: bold; 
            color: #fff; 
            background-color: #343a40; 
            padding: 8px 10px; 
            margin-bottom: 12px; 
            border-radius: 3px; 
        }
        .summary-grid { 
            display: inline-block; 
            width: 48%; 
            margin-right: 2%; 
            margin-bottom: 12px; 
            padding: 10px; 
            background: #f8f9fa; 
            border-left: 3px solid #333; 
        }
        .summary-label { 
            font-size: 10px; 
            color: #666; 
            font-weight: bold; 
            text-transform: uppercase; 
            margin-bottom: 4px; 
        }
        .summary-value { 
            font-size: 16px; 
            font-weight: bold; 
            color: #333; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        th { 
            background-color: #f0f0f0; 
            padding: 8px; 
            text-align: left; 
            font-weight: bold; 
            font-size: 10px; 
            border: 1px solid #dee2e6; 
        }
        td { 
            padding: 8px; 
            border: 1px solid #dee2e6; 
            font-size: 10px; 
        }
        tr:nth-child(even) { 
            background-color: #f8f9fa; 
        }
        .text-right { 
            text-align: right; 
        }
        .footer { 
            margin-top: 30px; 
            padding-top: 12px; 
            border-top: 1px solid #ddd; 
            text-align: center; 
            font-size: 10px; 
            color: #999; 
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-title">REPORTE MENSUAL</div>
        <div class="header-subtitle">ECOLINDUS - Sistema de Inventario</div>
        <div class="header-info">Período: {{ $dateFrom }} — {{ $dateTo }}</div>
    </div>

    <!-- RESUMEN FINANCIERO -->
    <div class="section">
        <div class="section-title">Resumen Financiero</div>
        <div class="summary-grid">
            <div class="summary-label">Total de Ventas</div>
            <div class="summary-value">${{ number_format($totalSales, 2) }}</div>
        </div>
        <div class="summary-grid">
            <div class="summary-label">IVA Recaudado</div>
            <div class="summary-value">${{ number_format($totalIva, 2) }}</div>
        </div>
    </div>

    <!-- FORMAS DE PAGO -->
    <div class="section">
        <div class="section-title">Totales por Forma de Pago</div>
        <div class="summary-grid">
            <div class="summary-label">Pagado en Efectivo</div>
            <div class="summary-value">${{ number_format($byCash, 2) }}</div>
        </div>
        <div class="summary-grid">
            <div class="summary-label">Pagado por Transferencia</div>
            <div class="summary-value">${{ number_format($byTransfer, 2) }}</div>
        </div>
    </div>

    <!-- ESTADÍSTICAS DE FACTURAS -->
    <div class="section">
        <div class="section-title">Estadísticas de Facturas</div>
        <div class="summary-grid">
            <div class="summary-label">Facturas Emitidas</div>
            <div class="summary-value">{{ $countEmitted }}</div>
        </div>
        <div class="summary-grid">
            <div class="summary-label">Facturas Pendientes</div>
            <div class="summary-value">{{ $countPending }}</div>
        </div>
    </div>

    <!-- DETALLE DE FACTURAS -->
    <div class="section">
        <div class="section-title">Listado Detallado de Facturas</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 15%;"># Factura</th>
                    <th style="width: 30%;">Cliente</th>
                    <th style="width: 15%; text-align: right;">Subtotal</th>
                    <th style="width: 15%; text-align: right;">IVA</th>
                    <th style="width: 15%; text-align: right;">Total</th>
                    <th style="width: 10%;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                <tr>
                    <td>{{ $invoice->invoice_number }}</td>
                    <td>{{ $invoice->customer?->first_name }} {{ $invoice->customer?->last_name }}</td>
                    <td class="text-right">${{ number_format($invoice->subtotal, 2) }}</td>
                    <td class="text-right">${{ number_format($invoice->tax_total, 2) }}</td>
                    <td class="text-right"><strong>${{ number_format($invoice->total, 2) }}</strong></td>
                    <td>{{ ucfirst($invoice->status) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px; color: #999;">No hay facturas para el período seleccionado</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        <div>Reporte generado el {{ now()->format('d/m/Y H:i') }} | Usuario: {{ Auth::user()->name ?? 'Sistema' }}</div>
        <div>Documento confidencial - Sistema de Inventario ECOLINDUS</div>
    </div>
</body>
</html>
