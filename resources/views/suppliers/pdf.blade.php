<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Proveedores - ECOLINDUS</title>
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
            border-left: 3px solid #333;
            padding: 12px; 
            margin-bottom: 20px; 
        }

        .summary-grid { 
            display: inline-block; 
            width: 30%; 
            text-align: center; 
            margin-right: 2%;
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

        tr:nth-child(even){ 
            background: #f8f9fa; 
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
        <h1>ECOLINDUS - Reporte de Proveedores</h1>
        <div class="header-info">
            Generado: {{ now()->format('d/m/Y H:i') }} | Usuario: {{ Auth::user()->name ?? 'Sistema' }}
        </div>
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
                <th style="width: 35%;">Proveedor</th>
                <th style="width: 20%;">Contacto</th>
                <th style="width: 20%;">Teléfono</th>
                <th style="width: 25%;">Email</th>
            </tr>
        </thead>
        <tbody>
            @forelse($suppliers as $supplier)
            <tr>
                <td>{{ $supplier->name }}</td>
                <td>{{ $supplier->contact ?? '-' }}</td>
                <td>{{ $supplier->phone ?? '-' }}</td>
                <td>{{ $supplier->email ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center; padding: 20px; color: #999;">No hay proveedores registrados</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div>Documento confidencial - Sistema de Inventario ECOLINDUS</div>
        <div>La información contenida es confidencial y está sujeta a verificación.</div>
    </div>
</body>
</html>