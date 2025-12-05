<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ventas por Cliente</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #f4f4f4; }
    </style>
</head>
<body>
    <h2>Ventas por Cliente</h2>
    <p>Periodo: {{ $date_from ?? 'Todos' }} - {{ $date_to ?? 'Todos' }}</p>
    <table>
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Facturas</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
        @foreach($results as $row)
            <tr>
                <td>{{ $row['customer_name'] }}</td>
                <td style="text-align:center">{{ $row['invoices_count'] }}</td>
                <td style="text-align:right">{{ number_format($row['total'],2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
