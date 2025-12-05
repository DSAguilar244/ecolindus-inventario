<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        /* Page setup for DomPDF - A4 with optimized margins for content */
        @page { size: A4; margin: 12mm 12mm; }
        html, body { margin: 0; padding: 0; }
        /* Embed DejaVu Sans explicitly from vendor (absolute path) */
        /* Optional: If needed, convert to base64 and use data: URL instead of file:// for cross-platform compatibility */
        /* To enable base64, generate with: base64 -w 0 vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf > font.b64 */
        /* Then replace src: url("file://...") with src: url("data:application/octet-stream;base64,...") */
        @font-face {
            font-family: 'DejaVuEmbedded';
            src: url("file://{{ realpath(base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf')) }}") format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @font-face {
            font-family: 'DejaVuEmbedded';
            src: url("file://{{ realpath(base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf')) }}") format('truetype');
            font-weight: bold;
            font-style: normal;
        }
        body { font-family: 'DejaVuEmbedded', 'DejaVu Sans', sans-serif; font-size: 11px; line-height: 1.4; color: #222; }

        /* Header / Footer: use flow layout (avoid position:fixed for better DomPDF compatibility) */
        .header { display: block; width: 100%; padding: 4px 0; border-bottom: 1px solid #ddd; margin-bottom: 6px; }
        .footer { display: block; width: 100%; padding: 4px 0; border-top: 1px solid #ddd; font-size: 9px; color: #666; margin-top: 6px; }

        /* Content area */
        .content { margin-top: 0; margin-bottom: 0; padding: 0; }

        /* Logo styling - scales well in PDF */
        .logo-cell img { max-width: 150px; height: auto; display: block; }

        h2 { font-size: 13px; margin: 3px 0 6px 0; font-weight: bold; }
        .meta { margin: 0 0 6px 0; font-size: 11px; }
        .right { text-align: right; }

        table { width: 100%; border-collapse: collapse; table-layout: fixed; word-wrap: break-word; }
        thead { display: table-header-group; }
        thead th { background: #f0f0f0; padding: 4px; border: 1px solid #999; font-size: 10px; font-weight: bold; }
        td, th { border: 1px solid #ccc; padding: 4px; vertical-align: middle; }
        tbody tr { page-break-inside: avoid; }
        tbody td { font-size: 10px; }

        .totals { margin-top: 6px; width: 100%; }
        .totals td { border: none; padding: 3px 4px; font-size: 10px; }

        /* Avoid splitting the totals box */
        .totals-wrap { display: block; width: 38%; page-break-inside: avoid; margin-left: auto; }

        /* Small helpers */
        .small { font-size: 10px; color: #555; }
    </style>
</head>
<body>
    <div class="content">
        <div class="header">
            <table style="width:100%; border:none; border-collapse:collapse;">
                <tr>
                    <td style="vertical-align:top; padding:0; border:none;" class="logo-cell">
                        @php $logoPath = public_path('images/ecolindus-logo.png'); @endphp
                        @if(file_exists($logoPath))
                            <img src="file://{{ $logoPath }}" alt="ECOLINDUS Logo">
                        @else
                            <strong style="font-size:12px; display:block; margin-bottom:2px;">ECOLINDUS</strong>
                            <div class="small">RUC / Identificación: {{ config('app.company_ruc') ?? '---' }}</div>
                        @endif
                    </td>
                    <td style="vertical-align:top; padding:0; border:none; text-align:right;">
                        <div class="small">Factura: <strong>{{ $invoice->invoice_number }}</strong></div>
                        <div class="small">Fecha: {{ optional($invoice->date)->format('Y-m-d') ?? optional($invoice->created_at)->format('Y-m-d') ?? '' }}</div>
                    </td>
                </tr>
            </table>
        </div>
        <h2>Factura {{ $invoice->invoice_number }}</h2>

        <p class="meta">Cliente: {{ optional($invoice->customer)->first_name }} {{ optional($invoice->customer)->last_name }} &middot; {{ optional($invoice->customer)->identification }}</p>
        @if(optional($invoice->customer)->address)
            <p class="meta">Dirección: {{ optional($invoice->customer)->address }}</p>
        @endif

        <table>
            <thead>
                <tr>
                    <th style="width:45%">Producto</th>
                    <th style="width:12%" class="right">Cantidad</th>
                    <th style="width:14%" class="right">Precio</th>
                    <th style="width:14%" class="right">Impuesto</th>
                    <th style="width:15%" class="right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ optional($item->product)->name }}</td>
                    <td class="right">{{ $item->quantity }}</td>
                    <td class="right">{{ number_format($item->unit_price,2) }}</td>
                    <td class="right">{{ $item->tax_rate }}% ({{ number_format((($item->tax_rate ?? 0)/100) * $item->line_total,2) }})</td>
                    <td class="right">{{ number_format($item->line_total + ((($item->tax_rate ?? 0)/100) * $item->line_total),2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top:8px; width:100%;">
            <div class="totals-wrap">
            <table class="totals-table" style="width:100%; border:none; border-collapse:collapse;">
                <tr>
                    <td style="border:none; padding:4px 6px;" class="small">Subtotal:</td>
                    <td style="border:none; padding:4px 6px; text-align:right;"><strong>{{ number_format($invoice->subtotal,2) }}</strong></td>
                </tr>
                <tr>
                    <td style="border:none; padding:4px 6px;" class="small">Impuesto:</td>
                    <td style="border:none; padding:4px 6px; text-align:right;"><strong>{{ number_format($invoice->tax_total,2) }}</strong></td>
                </tr>
                <tr>
                    <td style="border:none; padding:4px 6px;" class="small">Total:</td>
                    <td style="border:none; padding:4px 6px; text-align:right;"><strong>{{ number_format($invoice->total,2) }}</strong></td>
                </tr>
            </table>
            </div>
        </div>

        <div style="clear:both"></div>
    </div>

    {{-- DomPDF page script for page numbers (works when rendering through Barryvdh/DomPDF) --}}
    @if (isset($pdf))
        <script type="text/php">
            if (isset($pdf)) {
                $font = $fontMetrics->get_font("DejaVu Sans", "normal");
                $size = 9;
                $text = "Página {PAGE_NUM} / {PAGE_COUNT}";
                $width = $fontMetrics->get_text_width($text, $font, $size);
                $x = $pdf->get_width() - $width - 24;
                $y = $pdf->get_height() - 18;
                $pdf->page_text($x, $y, $text, $font, $size, array(0,0,0));
            }
        </script>
    @endif

    <div class="footer">
        <div style="text-align:center;">Generado por ECOLINDUS</div>
    </div>

</body>
</html>
