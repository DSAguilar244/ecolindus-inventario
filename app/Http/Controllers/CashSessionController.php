<?php

namespace App\Http\Controllers;

use App\Models\CashSession;
use App\Models\Invoice;
use App\Models\IdempotencyKey;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashSessionController extends Controller
{
    public function open(Request $request)
    {
        $user = Auth::user();

        // Validar que no haya sesión abierta
        $activeSession = CashSession::where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        if ($activeSession) {
            return redirect()->back()->with('error', 'Ya existe una sesión de caja abierta.');
        }

        $request->validate([
            'opening_amount' => 'required|numeric|min:0',
        ]);

        $session = CashSession::create([
            'user_id' => $user->id,
            'opened_at' => now(),
            'opening_amount' => $request->opening_amount,
            'status' => 'open',
        ]);

        return redirect()->back()->with('success', 'Caja abierta correctamente.');
    }

    public function close(Request $request)
    {
        $user = Auth::user();

        $session = CashSession::where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        if (!$session) {
            if ($request->isJson() || $request->wantsJson()) {
                return response()->json(['message' => 'No existe una sesión de caja abierta.'], 404);
            }
            return redirect()->back()->with('error', 'No existe una sesión de caja abierta.');
        }

        // Check idempotency for JSON/API requests
        $idempotencyKey = $request->input('idempotency_key');
        if ($request->isJson() || $request->wantsJson()) {
            if (!empty($idempotencyKey)) {
                $existing = IdempotencyKey::where('key', $idempotencyKey)->first();
                if ($existing) {
                    return response()->json(['message' => 'Duplicate request'], 409);
                }
            }
        }

        // Check for pending invoices in the session period
        $pendingExists = Invoice::where('user_id', $user->id)
            ->where('status', Invoice::STATUS_PENDIENTE)
            ->whereBetween('date', [$session->opened_at, now()])
            ->exists();

        if ($pendingExists) {
            $msg = 'No se puede cerrar la caja: existen facturas pendientes en el periodo de la sesión.';
            if ($request->isJson() || $request->wantsJson()) {
                return response()->json(['message' => $msg], 400);
            }
            return redirect()->back()->with('error', $msg);
        }

        // Calcular totales de facturas emitidas en esta sesión
        $invoices = Invoice::where('user_id', $user->id)
            ->where('status', Invoice::STATUS_EMITIDA)
            ->whereBetween('date', [$session->opened_at, now()])
            ->with('payment')
            ->get();

        // Ensure all emitted invoices have payment breakdown
        $withoutPayment = $invoices->filter(function ($inv) { return !$inv->payment; });
        if ($withoutPayment->count() > 0) {
            $numbers = $withoutPayment->pluck('invoice_number')->join(', ');
            $msg = "No se puede cerrar la caja: las siguientes facturas no tienen desglose de pago: {$numbers}";
            if ($request->isJson() || $request->wantsJson()) {
                return response()->json(['message' => $msg], 400);
            }
            return redirect()->back()->with('error', $msg);
        }

        $totalCash = (float) $invoices->sum(function ($inv) { return $inv->payment->cash_amount ?? 0; });
        $totalTransfer = (float) $invoices->sum(function ($inv) { return $inv->payment->transfer_amount ?? 0; });
        $totalInvoiced = (float) $invoices->sum('total');

        // Set closing amount based on recorded payments within the session (cash + transfer)
        $calculatedClosing = round($totalCash + $totalTransfer, 2);

        // Reported closing amount provided by UI (optional)
        $reported = null;
        if ($request->has('reported_closing_amount') && $request->input('reported_closing_amount') !== '') {
            $reported = (float) $request->input('reported_closing_amount');
        }

        // Expected closing is opening + cash + transfer
        $expected = round(((float) $session->opening_amount) + $totalCash + $totalTransfer, 2);

        $difference = null;
        if (!is_null($reported)) {
            $difference = round($expected - $reported, 2);
        }

        $notes = "Efectivo: {$totalCash}, Transferencias: {$totalTransfer}";
        if (!is_null($reported)) {
            $notes .= ", Reportado: {$reported}";
        }

        // If front-end provided an explanatory note, append it (trim to reasonable length)
        $clientNote = $request->input('notes');
        if (! empty($clientNote)) {
            $clean = trim(substr($clientNote, 0, 1000));
            $notes .= ", Nota: {$clean}";
        }

        // For JSON/API consumers enforce that a note is provided when there's a material difference
        if (($request->isJson() || $request->wantsJson()) && !is_null($difference) && abs($difference) >= 0.01 && empty(trim((string) $clientNote))) {
            $msg = 'Por favor proporciona una nota explicativa para la diferencia';
            return response()->json(['message' => $msg], 422);
        }

        $session->update([
            'closed_at' => now(),
            'closing_amount' => $calculatedClosing,
            'total_invoiced' => $totalInvoiced,
            'total_cash' => $totalCash,
            'total_transfer' => $totalTransfer,
            'expected_closing' => $expected,
            'reported_closing_amount' => $reported,
            'difference' => $difference,
            'status' => 'closed',
            'notes' => $notes,
        ]);

        // Store idempotency key if provided and request is JSON
        if (($request->isJson() || $request->wantsJson()) && !empty($idempotencyKey)) {
            IdempotencyKey::create([
                'key' => $idempotencyKey,
                'cash_session_id' => $session->id,
            ]);
        }

        if ($request->isJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Caja cerrada correctamente.',
                'expected_closing' => $expected,
                'reported_closing_amount' => $reported,
                'difference' => $difference,
            ]);
        }

        return redirect()->back()->with('success', 'Caja cerrada correctamente.');
    }

    public function summary(Request $request)
    {
        $user = Auth::user();

        // If this is a normal browser navigation, redirect to the dashboard
        // where the "Abrir Caja" button lives. Keep JSON responses for AJAX/API consumers.
        if (! $request->isJson() && ! $request->wantsJson()) {
            return redirect()->route('dashboard');
        }

        $session = CashSession::where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        if (!$session) {
            return response()->json(['error' => 'No hay sesión activa'], 404);
        }

        $invoices = Invoice::where('user_id', $user->id)
            ->where('status', Invoice::STATUS_EMITIDA)
            ->whereBetween('date', [$session->opened_at, now()])
            ->with(['customer', 'payment'])
            ->get();

        $totalCash = 0.0;
        $totalTransfer = 0.0;
        $totalInvoices = 0.0;
        $totalSubtotal = 0.0;
        $totalTax = 0.0;

        $invoiceList = [];
        foreach ($invoices as $invoice) {
            $paymentCash = $invoice->payment?->cash_amount ?? 0;
            $paymentTransfer = $invoice->payment?->transfer_amount ?? 0;

            $totalCash += (float) $paymentCash;
            $totalTransfer += (float) $paymentTransfer;
            $totalInvoices += (float) $invoice->total;
            $totalSubtotal += (float) $invoice->subtotal;
            $totalTax += (float) $invoice->tax_total;

            $invoiceList[] = [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer' => $invoice->customer?->first_name . ' ' . $invoice->customer?->last_name,
                'subtotal' => (float) $invoice->subtotal,
                'tax' => (float) $invoice->tax_total,
                'total' => (float) $invoice->total,
                'payment' => [
                    'cash' => (float) $paymentCash,
                    'transfer' => (float) $paymentTransfer,
                ],
            ];
        }

        return response()->json([
            'session' => [
                'id' => $session->id,
                'user_id' => $session->user_id,
                'opened_at' => $session->opened_at,
                'opening_amount' => (float) $session->opening_amount,
                'total_invoiced' => (float) ($session->total_invoiced ?? 0),
                'total_cash' => (float) ($session->total_cash ?? $totalCash),
                'total_transfer' => (float) ($session->total_transfer ?? $totalTransfer),
                'expected_closing' => (float) ($session->expected_closing ?? round(((float)$session->opening_amount) + $totalCash + $totalTransfer, 2)),
                'reported_closing_amount' => (float) ($session->reported_closing_amount ?? 0),
                'difference' => is_null($session->difference) ? null : (float) $session->difference,
            ],
            'totals' => [
                'invoices_count' => count($invoiceList),
                'subtotal' => round($totalSubtotal, 2),
                'tax' => round($totalTax, 2),
                'total_invoices' => round($totalInvoices, 2),
                'total_cash' => round($totalCash, 2),
                'total_transfer' => round($totalTransfer, 2),
                'expected_closing' => round(((float)$session->opening_amount) + $totalCash + $totalTransfer, 2),
            ],
            'invoices' => $invoiceList,
        ]);
    }

    public function history()
    {
        $userId = request()->query('user_id');
        $dateFrom = request()->query('date_from');
        $dateTo = request()->query('date_to');

        $query = CashSession::with('user')->orderByDesc('opened_at');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($dateFrom) {
            $query->where('opened_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('closed_at', '<=', $dateTo);
        }

        $sessions = $query->paginate(25)->withQueryString();

        return view('cash_sessions.history', compact('sessions'));
    }

    /**
     * Clear closed cash sessions history.
     * This will delete sessions with status = 'closed' only.
     * Does not touch open sessions nor invoices.
     */
    public function clearHistory(Request $request)
    {
        $user = Auth::user();

        if (! $user || ! ($user->is_admin ?? false) ) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'No autorizado'], 403);
            }
            abort(403);
        }

        // Build query for sessions to delete (only closed)
        $query = CashSession::where('status', 'closed');

        // Optional filter: older than N days (default 0 = no filter, delete all closed)
        $days = (int) $request->input('older_than_days', 0);
        if ($days > 0) {
            $query->where('closed_at', '<', now()->subDays($days));
        }

        // Optional filter: by user_id
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        $deleted = $query->delete();

        if ($request->wantsJson()) {
            return response()->json(['deleted' => $deleted]);
        }

        return redirect()->back()->with('success', "Se eliminaron {$deleted} sesiones de caja cerradas.");
    }

    /**
     * Export the current or last cash session summary as PDF
     */
    public function exportPdf()
    {
        $user = Auth::user();
        // Try to get open session first, then fall back to most recent closed session
        $session = CashSession::where('user_id', $user->id)
            ->where('status', 'open')
            ->first();
        
        if (!$session) {
            $session = CashSession::where('user_id', $user->id)
                ->where('status', 'closed')
                ->orderByDesc('closed_at')
                ->first();
        }

        if (!$session) {
            return response()->json(['error' => 'No hay caja abierta ni historial disponible'], 404);
        }

        // Get the same data as summary()
        $invoices = Invoice::where('user_id', $user->id)
            ->where('status', Invoice::STATUS_EMITIDA)
            ->whereBetween('date', [$session->opened_at, $session->closed_at ?? now()])
            ->with('customer', 'payment')
            ->get();

        $totalCash = 0;
        $totalTransfer = 0;
        $totalInvoices = 0;
        $totalSubtotal = 0;
        $totalTax = 0;
        $invoiceList = [];

        foreach ($invoices as $invoice) {
            $paymentCash = $invoice->payment?->cash_amount ?? 0;
            $paymentTransfer = $invoice->payment?->transfer_amount ?? 0;

            $totalCash += (float) $paymentCash;
            $totalTransfer += (float) $paymentTransfer;
            $totalInvoices += (float) $invoice->total;
            $totalSubtotal += (float) $invoice->subtotal;
            $totalTax += (float) $invoice->tax_total;

            $invoiceList[] = [
                'invoice_number' => $invoice->invoice_number,
                'customer' => $invoice->customer?->first_name . ' ' . $invoice->customer?->last_name,
                'subtotal' => (float) $invoice->subtotal,
                'tax' => (float) $invoice->tax_total,
                'total' => (float) $invoice->total,
                'cash' => (float) $paymentCash,
                'transfer' => (float) $paymentTransfer,
            ];
        }

        $expected = round(((float) $session->opening_amount) + $totalCash + $totalTransfer, 2);

        $data = [
            'session' => $session,
            'opening_amount' => (float) $session->opening_amount,
            'total_cash' => round($totalCash, 2),
            'total_transfer' => round($totalTransfer, 2),
            'expected_closing' => $expected,
            'total_invoiced' => round($totalInvoices, 2),
            'total_subtotal' => round($totalSubtotal, 2),
            'total_tax' => round($totalTax, 2),
            'invoices' => $invoiceList,
            'user' => $user,
            'generated_at' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('cash_sessions.pdf_export', $data);
        $filename = 'caja_' . $session->opened_at->format('Ymd_His') . '.pdf';
        
        return $pdf->download($filename);
    }
}

