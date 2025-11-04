<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Productos</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 5px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Reporte de Productos</h2>
    <p>Generado el {{ now()->format('d/m/Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Stock</th>
                <th>Stock mínimo</th>
                <th>Unidad</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr>
                <td>{{ $product->name }}</td>
                <td>{{ $product->stock }}</td>
                <td>{{ $product->min_stock }}</td>
                <td>{{ $product->unit }}</td>
                <td>{{ $product->stock < $product->min_stock ? 'Crítico' : 'OK' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>