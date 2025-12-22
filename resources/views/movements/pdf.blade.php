<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de Movimientos - ECOLINDUS</title>
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

        .header h1 { 
            font-size: 18px; 
            font-weight: bold;
            margin-bottom: 6px; 
        }

        .header-info { 
            font-size: 10px; 
            color: #666; 
        }

        .summary { 
            background: #f8f9fa; 
            padding: 12px; 
            border-left: 3px solid #333;
            margin-bottom: 20px; 
        }

        .summary-grid { 
            display: inline-block; 
            width: 18%; 
            text-align: center; 
            margin-right: 1%;
        }

        .summary-label { 
            font-size: 9px; 
            color: #666; 
            text-transform: uppercase; 
            font-weight: bold;
            margin-bottom: 4px; 
        }

        .summary-value { 
            font-size: 14px;
            font-weight: bold; 
            color: #333;
        }

        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 12px; 
            font-size: 10px; 
        }

        th { 
            background-color: #343a40; 
            color: #fff; 
            padding: 10px; 
            text-align: left; 
            font-weight: bold; 
            border: 1px solid #343a40;
        }

        td { 
            padding: 8px; 
            border: 1px solid #dee2e6; 
        }

        tr:nth-child(even) { 
            background: #f8f9fa; 
        }

        .type-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 2px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .type-entrada {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .type-salida{
            background-color: #ffebee;
            color: #d32f2f;
        }

        .type-dañado{
            background-color: #fff3e0;
            color: #f57c00;
        }

        .type-devuelto{
            background-color: #f3e5f5;
            color: #7b1fa2;
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
        <h1>ECOLINDUS - Reporte de Movimientos de Inventario</h1>
        <div class="header-info">
            Generado: {{ now()->format('d/m/Y H:i') }} | Usuario: {{ Auth::user()->name ?? 'Sistema' }}
        </div>
    </div>

    <div class="summary">
        <div class="summary-grid">
            <div class="summary-label">Total</div>
            <div class="summary-value">{{ $summary['total'] ?? 0 }}</div>
        </div>
        <div class="summary-grid">
            <div class="summary-label">Entradas</div>
            <div class="summary-value">{{ $summary['entrada'] ?? 0 }}</div>
        </div>
        <div class="summary-grid">
            <div class="summary-label">Salidas</div>
            <div class="summary-value">{{ $summary['salida'] ?? 0 }}</div>
        </div>
        <div class="summary-grid">
            <div class="summary-label">Dañados</div>
            <div class="summary-value">{{ $summary['dañado'] ?? 0 }}</div>
        </div>
        <div class="summary-grid">
            <div class="summary-label">Devueltos</div>
            <div class="summary-value">{{ $summary['devuelto'] ?? 0 }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%;">Fecha</th>
                <th style="width: 25%;">Producto</th>
                <th style="width: 12%;">Tipo</th>
                <th style="width: 8%;">Cantidad</th>
                <th style="width: 18%;">Proveedor</th>
                <th style="width: 12%;">Usuario</th>
                <th style="width: 15%;">Motivo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movements as $movement)
            <tr>
                <td>{{ $movement->created_at->format('d/m/Y') }}</td>
                <td>{{ $movement->product->name ?? '—' }}</td>
                <td>
                    <span class="type-badge type-{{ strtolower($movement->type) }}">{{ ucfirst($movement->type) }}</span>
                </td>
                <td style="text-align: center;">{{ $movement->quantity }}</td>
                <td>{{ $movement->supplier->name ?? '-' }}</td>
                <td>{{ $movement->user->name ?? 'Sistema' }}</td>
                <td title="{{ $movement->reason }}">{{ \Illuminate\Support\Str::limit($movement->reason ?? '-', 20, '...') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 20px; color: #999;">No hay movimientos para los filtros seleccionados.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div>Documento confidencial - Sistema de Inventario ECOLINDUS</div>
    </div>
</body>
</html>