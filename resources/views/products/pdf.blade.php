<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Inventario - ECOLINDUS</title>
    <style>
        /* Estilos base */
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        /* Encabezado */
        .header {
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 24px;
            margin: 0;
            padding: 0;
        }

        .header-info {
            margin-top: 10px;
            color: #666;
        }

        /* Resumen */
        .summary {
            background: #f8f9fa;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .summary-grid {
            display: inline-block;
            width: 24%;
            text-align: center;
            padding: 10px 0;
        }

        .summary-label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
        }

        .summary-value {
            font-size: 16px;
            font-weight: bold;
            margin-top: 5px;
        }

        /* Tabla principal */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 11px;
        }

        th {
            background-color: #343a40;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: normal;
        }

        td {
            padding: 10px 8px;
            border-bottom: 1px solid #dee2e6;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        /* Estados */
        .status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-critical {
            background-color: #ffebee;
            color: #d32f2f;
        }

        .status-warning {
            background-color: #fff3e0;
            color: #f57c00;
        }

        .status-ok {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        /* Pie de página */
        .footer {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            padding-top: 10px;
            border-top: 1px solid #eee;
            font-size: 10px;
            color: #666;
            text-align: center;
        }

        /* Categorías */
        .category {
            display: inline-block;
            padding: 3px 6px;
            background-color: #e9ecef;
            border-radius: 3px;
            font-size: 10px;
        }

        /* Paginación */
        .page-number {
            position: fixed;
            bottom: 20px;
            right: 20px;
            font-size: 10px;
            color: #666;
        }

        /* Sección de firma */
        .signature-section {
            margin-top: 50px;
            page-break-inside: avoid;
        }

        .signature-line {
            width: 200px;
            margin: 40px auto 10px;
            border-top: 1px solid #333;
        }

        .signature-title {
            text-align: center;
            font-size: 11px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>ECOLINDUS - Reporte de Inventario</h1>
        <div class="header-info">
            <strong>Fecha de generación:</strong> {{ now()->format('d/m/Y H:i') }}<br>
            <strong>Generado por:</strong> {{ Auth::user()->name ?? 'Sistema' }}
        </div>
    </div>

    <div class="summary">
        <div class="summary-grid">
            <div class="summary-label">Total Productos</div>
            <div class="summary-value">{{ $summary['total_products'] ?? $products->count() }}</div>
        </div>
        <div class="summary-grid">
            <div class="summary-label">Stock Crítico</div>
            <div class="summary-value">{{ $summary['critical'] ?? 0 }}</div>
        </div>
        <div class="summary-grid">
            <div class="summary-label">Stock Total</div>
            <div class="summary-value">{{ $summary['total_stock'] ?? $products->sum('stock') }}</div>
        </div>
        <div class="summary-grid">
            <div class="summary-label">Categorías</div>
            <div class="summary-value">{{ $summary['categories'] ?? $products->unique('category')->count() }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30%;">Producto</th>
                <th style="width: 15%;">Categoría</th>
                <th style="width: 15%;">Stock Actual</th>
                <th style="width: 15%;">Stock Mínimo</th>
                <th style="width: 10%;">Unidad</th>
                <th style="width: 15%;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr>
                <td>{{ $product->name }}</td>
                <td>
                    <span class="category">{{ $product->categoryModel->name ?? '' }}</span>
                </td>
                <td>{{ number_format($product->stock, 0) }}</td>
                <td>{{ number_format($product->min_stock, 0) }}</td>
                <td>{{ $product->unit }}</td>
                <td>
                    @php
                        $status = '';
                        $statusClass = '';
                        if ($product->stock < $product->min_stock) {
                            $status = 'Crítico';
                            $statusClass = 'status-critical';
                        } elseif ($product->stock < $product->min_stock * 1.5) {
                            $status = 'Bajo';
                            $statusClass = 'status-warning';
                        } else {
                            $status = 'Óptimo';
                            $statusClass = 'status-ok';
                        }
                    @endphp
                    <span class="status {{ $statusClass }}">{{ $status }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature-section">
        <div class="signature-line"></div>
        <div class="signature-title">Firma del Responsable de Inventario</div>
    </div>

    <div class="footer">
        Este documento es generado automáticamente por el sistema de inventario ECOLINDUS.
        La información contenida es confidencial y está sujeta a verificación.
    </div>

    <div class="page-number">
        Página <span class="pagenum"></span>
    </div>
</body>
</html>