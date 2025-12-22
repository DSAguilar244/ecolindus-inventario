<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Proveedores - ECOLINDUS</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        .header { border-bottom: 2px solid #000; padding-bottom: 12px; margin-bottom: 18px; }
        .header h1 { font-size: 20px; margin: 0; }
        .header-info { color: #666; margin-top: 6px; }

        .summary { background: #f8f9fa; padding: 10px; border-radius: 4px; margin-bottom: 18px; }
        .summary-grid { display: inline-block; width: 32%; text-align: center; }
        .summary-label { font-size: 10px; color:#666; text-transform: uppercase; }
        .summary-value { font-weight: bold; margin-top: 4px; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 11px; }
        th { background-color: #343a40; color: #fff; padding: 10px 8px; text-align: left; font-weight: normal; }
        td { padding: 8px; border-bottom: 1px solid #dee2e6; }
        tr:nth-child(even){ background: #f8f9fa; }

        .footer { position: fixed; bottom: 20px; left: 20px; right: 20px; text-align:center; font-size:10px; color:#666; }
        .page-number { position: fixed; bottom: 20px; right: 20px; font-size: 10px; color:#666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>ECOLINDUS - Reporte de Proveedores</h1>
        <div class="header-info">Generado el {{ now()->format('d/m/Y H:i') }} — {{ Auth::user()->name ?? 'Sistema' }}</div>
    </div>

    <div class="summary">
        <div class="summary-grid">
            <div class="summary-label">Total Proveedores</div>
            <div class="summary-value">{{ $suppliers->count() }}</div>
        </div>
        <div class="summary-grid">
            <div class="summary-label">Con Email</div>
            <div class="summary-value">{{ $suppliers->filter(fn($s) => !empty($s->email))->count() }}</div>
        </div>
        <div class="summary-grid">
            <div class="summary-label">Con Teléfono</div>
            <div class="summary-value">{{ $suppliers->filter(fn($s) => !empty($s->phone))->count() }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:40%">Proveedor</th>
                <th style="width:20%">Contacto</th>
                <th style="width:20%">Teléfono</th>
                <th style="width:20%">Email</th>
            </tr>
        </thead>
        <tbody>
            @foreach($suppliers as $supplier)
            <tr>
                <td>{{ $supplier->name }}</td>
                <td>{{ $supplier->contact ?? '-' }}</td>
                <td>{{ $supplier->phone ?? '-' }}</td>
                <td>{{ $supplier->email ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">Este documento fue generado automáticamente por ECOLINDUS. Verifique los datos antes de su uso oficial.</div>
    <div class="page-number">Página <span class="pagenum"></span></div>
</body>
</html>