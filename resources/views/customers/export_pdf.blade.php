<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Clientes - Ecolindus</title>
    <style>
        body { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; font-size:12px; color: #333; }
        @page { margin: 18mm 12mm; }
        .header { text-align:center; margin-bottom: 10px }
        .title { font-size: 16px; font-weight: bold; }
        .small { font-size: 10px; color:#666; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        thead { display: table-header-group; }
        table thead th { background: #f5f5f5; padding:6px; border: 1px solid #ddd; text-align: left; }
        table tbody td { padding:6px; border: 1px solid #ddd; }
        .meta { margin-top: 12px; font-size: 11px; }
        .footer { margin-top: 20px; font-size: 10px; color:#444; text-align:center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Ecolindus - Lista de Clientes</div>
        <div class="small">Generado: {{ now()->format('Y-m-d H:i:s') }}</div>
    </div>
    <div class="meta">Total Clientes: {{ $customers->count() }}</div>
    <table>
        <thead>
            <tr>
                <th style="width:15%">Identificación</th>
                <th style="width:30%">Nombre</th>
                <th style="width:15%">Teléfono</th>
                <th style="width:25%">Email</th>
                <th style="width:15%">Dirección</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $c)
            <tr>
                <td>{{ $c->identification }}</td>
                <td>{{ $c->first_name }} {{ $c->last_name }}</td>
                <td>{{ $c->phone }}</td>
                <td>{{ $c->email }}</td>
                <td>{{ $c->address }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">ECOLINDUS - Inventario</div>
    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_text(520, 820, "Página {PAGE_NUM} / {PAGE_COUNT}", null, 10, array(0,0,0));
        }
    </script>
</body>
</html>
