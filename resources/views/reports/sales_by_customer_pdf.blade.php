<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Ventas por Cliente - ECOLINDUS</title>
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
            border-bottom: 2px solid #333; 
            padding-bottom: 12px; 
            margin-bottom: 20px; 
        }
        .header-title { 
            font-size: 18px; 
            font-weight: bold; 
            margin-bottom: 6px; 
        }
        .header-info { 
            font-size: 10px; 
            color: #666; 
        }
        .period-info { 
            background: #f8f9fa; 
            padding: 10px; 
            border-left: 3px solid #333; 
            margin-bottom: 16px; 
        }
        .period-label { 
            font-size: 10px; 
            font-weight: bold; 
            color: #333; 
        }
        .period-dates { 
            font-size: 11px; 
            margin-top: 2px; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        th { 
            background-color: #343a40; 
            color: #fff; 
            padding: 10px; 
            text-align: left; 
            font-weight: bold; 
            font-size: 10px; 
            border: 1px solid #343a40; 
        }
        td { 
            padding: 10px; 
            border: 1px solid #dee2e6; 
            font-size: 11px; 
        }
        tr:nth-child(even) { 
            background-color: #f8f9fa; 
        }
        .right { 
            text-align: right; 
        }
        .total-row { 
            background-color: #e9ecef; 
            font-weight: bold; 
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
        <div class="header-title">ECOLINDUS - Reporte de Ventas por Cliente</div>
        <div class="header-info">Generado: {{ now()->format('d/m/Y H:i') }} | Usuario: {{ Auth::user()->name ?? 'Sistema' }}</div>
    </div>

    <div class="period-info">
        <div class="period-label">Período de Análisis:</div>
        <div class="period-dates">
            {{ $date_from ? \Carbon\Carbon::parse($date_from)->format('d/m/Y') : 'Desde inicio' }} 
            hasta 
            {{ $date_to ? \Carbon\Carbon::parse($date_to)->format('d/m/Y') : 'Hoy' }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 50%;">Cliente</th>
                <th style="width: 25%; text-align: center;">Número de Facturas</th>
                <th style="width: 25%; text-align: right;">Monto Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($results as $row)
            <tr>
                <td>{{ $row['customer_name'] }}</td>
                <td class="right">{{ $row['invoices_count'] }}</td>
                <td class="right">${{ number_format($row['total'], 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="text-align: center; padding: 20px; color: #999;">No hay datos para el período seleccionado</td>
            </tr>
            @endforelse
            @if($results->count() > 0)
            <tr class="total-row">
                <td style="text-align: right;">TOTAL:</td>
                <td class="right">{{ $results->sum('invoices_count') }}</td>
                <td class="right">${{ number_format($results->sum('total'), 2) }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <div>Documento confidencial - Sistema de Inventario ECOLINDUS</div>
    </div>
</body>
</html>
