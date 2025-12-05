<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>ECOLINDUS - Reporte de Movimientos</title>
    <style>
    /* Simple, DomPDF-friendly styles similar to suppliers/products PDFs */
    body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: #000; margin: 0; padding: 20px; }
    .header { border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 12px; }
    .header h1 { font-size: 16px; margin: 0; }
    .header-info { font-size: 12px; color: #333; }
    .summary { margin: 12px 0 18px 0; }
    .summary-grid { display: inline-block; width: 19%; text-align: center; }
    .summary-label { font-size: 10px; color: #666; text-transform: uppercase; }
    .summary-value { font-weight: bold; margin-top: 4px; }
    table { width:100%; border-collapse: collapse; margin-top: 8px; font-size: 11px; }
    th { background-color: #f5f5f5; padding: 8px; text-align: left; font-weight: 700; border: 1px solid #000; }
    td { padding: 8px; border: 1px solid #000; }
    tr:nth-child(even) { background: #f9f9f9; }
    .footer-note { margin-top:14px; font-size:11px; text-align:center; color:#666; }
    </style>
</head>
<body>

    {{-- Header (table layout for DomPDF compatibility) --}}
    <table style="width:100%; border-collapse:collapse; margin-bottom:8px;">
        <tr>
            <td style="text-align:left; vertical-align:top; font-weight:700; font-size:16px;">ECOLINDUS — Inventario</td>
            <td style="text-align:right; vertical-align:top; font-size:12px;">Reporte de Movimientos<br>Generado: {{ now()->format('d/m/Y H:i') }}</td>
        </tr>
    </table>

    {{-- Summary row as a small table --}}
    <table style="width:100%; border-collapse:collapse; margin-bottom:12px;">
        <tr>
            <td style="border:1px solid #000; padding:6px; text-align:center; font-weight:700;">Total</td>
            <td style="border:1px solid #000; padding:6px; text-align:center; font-weight:700;">Entradas</td>
            <td style="border:1px solid #000; padding:6px; text-align:center; font-weight:700;">Salidas</td>
            <td style="border:1px solid #000; padding:6px; text-align:center; font-weight:700;">Dañados</td>
            <td style="border:1px solid #000; padding:6px; text-align:center; font-weight:700;">Devueltos</td>
        </tr>
        <tr>
            <td style="border:1px solid #000; padding:8px; text-align:center;">{{ $summary['total'] ?? 0 }}</td>
            <td style="border:1px solid #000; padding:8px; text-align:center;">{{ $summary['entrada'] ?? 0 }}</td>
            <td style="border:1px solid #000; padding:8px; text-align:center;">{{ $summary['salida'] ?? 0 }}</td>
            <td style="border:1px solid #000; padding:8px; text-align:center;">{{ $summary['dañado'] ?? 0 }}</td>
            <td style="border:1px solid #000; padding:8px; text-align:center;">{{ $summary['devuelto'] ?? 0 }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th style="width:10%">Fecha</th>
                <th style="width:30%">Producto</th>
                <th style="width:10%">Tipo</th>
                <th style="width:8%">Cantidad</th>
                <th style="width:20%">Proveedor</th>
                <th style="width:12%">Usuario</th>
                <th style="width:10%">Motivo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movements as $movement)
                <tr>
                    <td>{{ $movement->created_at->format('d/m/Y') }}</td>
                    <td>{{ $movement->product->name ?? '—' }}</td>
                    <td>{{ ucfirst($movement->type) }}</td>
                    <td style="text-align:center">{{ $movement->quantity }}</td>
                    <td>{{ $movement->supplier->name ?? '-' }}</td>
                    <td>{{ $movement->user->name ?? 'Sistema' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($movement->reason, 40, '...') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:20px">No hay movimientos para los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-note">ECOLINDUS - Sistema de Inventario</div>

</body>
</html>