<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caja - {{ $session->opened_at->format('d/m/Y') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            font-size: 11px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 15px;
        }
        
        .header h1 {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .header p {
            color: #666;
            font-size: 12px;
            margin: 3px 0;
        }
        
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .section-title {
            background-color: #34495e;
            color: white;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
            border-radius: 3px;
        }
        
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            width: 40%;
            padding: 6px;
            background-color: #ecf0f1;
            font-weight: bold;
            border: 1px solid #bdc3c7;
        }
        
        .info-value {
            display: table-cell;
            width: 60%;
            padding: 6px;
            border: 1px solid #bdc3c7;
            text-align: right;
        }
        
        .arqueo-section {
            background-color: #f8f9fa;
            padding: 12px;
            border-left: 4px solid #3498db;
            margin-bottom: 15px;
            border-radius: 3px;
        }
        
        .arqueo-item {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            border-bottom: 1px dotted #bdc3c7;
        }
        
        .arqueo-item:last-child {
            border-bottom: none;
            padding-top: 8px;
            padding-bottom: 0;
            font-weight: bold;
            font-size: 12px;
            border-top: 1px solid #bdc3c7;
            margin-top: 4px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        thead {
            background-color: #ecf0f1;
        }
        
        th {
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #bdc3c7;
            font-size: 11px;
        }
        
        td {
            padding: 6px;
            border: 1px solid #bdc3c7;
            font-size: 10px;
        }
        
        .text-right {
            text-align: right;
        }
        
        .total-row {
            background-color: #ecf0f1;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #bdc3c7;
            text-align: center;
            font-size: 10px;
            color: #7f8c8d;
        }
        
        .currency {
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>ARQUEO DE CAJA</h1>
            <p><strong>Fecha:</strong> {{ $session->opened_at->format('d/m/Y H:i') }}</p>
            <p><strong>Usuario:</strong> {{ $user->name }}</p>
            @if($session->closed_at)
                <p><strong>Cierre:</strong> {{ $session->closed_at->format('d/m/Y H:i') }}</p>
            @endif
        </div>

        <!-- Resumen de Caja -->
        <div class="section">
            <div class="section-title">RESUMEN DE CAJA</div>
            
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Monto Inicial:</div>
                    <div class="info-value"><span class="currency">${{ number_format($opening_amount, 2) }}</span></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Total Efectivo Recibido:</div>
                    <div class="info-value"><span class="currency">${{ number_format($total_cash, 2) }}</span></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Total Transferencias:</div>
                    <div class="info-value"><span class="currency">${{ number_format($total_transfer, 2) }}</span></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Total Facturado:</div>
                    <div class="info-value"><span class="currency">${{ number_format($total_invoiced, 2) }}</span></div>
                </div>
            </div>
        </div>

        <!-- Arqueo Detallado -->
        <div class="section">
            <div class="section-title">CÁLCULO DE ARQUEO</div>
            
            <div class="arqueo-section">
                <div class="arqueo-item">
                    <span>Monto Inicial</span>
                    <span class="currency">${{ number_format($opening_amount, 2) }}</span>
                </div>
                <div class="arqueo-item">
                    <span>+ Efectivo</span>
                    <span class="currency">${{ number_format($total_cash, 2) }}</span>
                </div>
                <div class="arqueo-item">
                    <span>+ Transferencia</span>
                    <span class="currency">${{ number_format($total_transfer, 2) }}</span>
                </div>
                <div class="arqueo-item">
                    <span>= Monto Esperado en Caja</span>
                    <span class="currency">${{ number_format($expected_closing, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Detalle de Facturas -->
        @if(count($invoices) > 0)
        <div class="section">
            <div class="section-title">DETALLE DE FACTURAS</div>
            
            <table>
                <thead>
                    <tr>
                        <th>Factura</th>
                        <th>Cliente</th>
                        <th class="text-right">Subtotal</th>
                        <th class="text-right">IVA</th>
                        <th class="text-right">Total</th>
                        <th class="text-right">Efectivo</th>
                        <th class="text-right">Transf.</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $inv)
                    <tr>
                        <td>{{ $inv['invoice_number'] ?? 'N/A' }}</td>
                        <td>{{ $inv['customer'] ?? 'N/A' }}</td>
                        <td class="text-right"><span class="currency">${{ number_format($inv['subtotal'] ?? 0, 2) }}</span></td>
                        <td class="text-right"><span class="currency">${{ number_format($inv['tax'] ?? 0, 2) }}</span></td>
                        <td class="text-right"><span class="currency">${{ number_format($inv['total'] ?? 0, 2) }}</span></td>
                        <td class="text-right"><span class="currency">${{ number_format($inv['cash'] ?? 0, 2) }}</span></td>
                        <td class="text-right"><span class="currency">${{ number_format($inv['transfer'] ?? 0, 2) }}</span></td>
                    </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="2">TOTALES</td>
                        <td class="text-right"><span class="currency">${{ number_format($total_subtotal, 2) }}</span></td>
                        <td class="text-right"><span class="currency">${{ number_format($total_tax, 2) }}</span></td>
                        <td class="text-right"><span class="currency">${{ number_format($total_invoiced, 2) }}</span></td>
                        <td class="text-right"><span class="currency">${{ number_format($total_cash, 2) }}</span></td>
                        <td class="text-right"><span class="currency">${{ number_format($total_transfer, 2) }}</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
        @else
        <div class="section">
            <p style="text-align: center; color: #999; padding: 20px;">No hay facturas registradas en esta sesión.</p>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Documento generado automáticamente el {{ $generated_at }}</p>
            <p>ECOLINDUS - Sistema de Inventario</p>
        </div>
    </div>
</body>
</html>
