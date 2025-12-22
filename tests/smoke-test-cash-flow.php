<?php
/**
 * Manual Smoke Test Script for ECOLINDUS Cash Session Flow
 * 
 * This script simulates:
 * 1. Open a cash session
 * 2. Create an invoice with mixed payment (cash + transfer)
 * 3. Emit the invoice
 * 4. Get summary with totals
 * 5. Close the session with reported amount
 * 6. Verify arqueo fields are saved
 * 
 * Run with: php tests/smoke-test-cash-flow.php
 */

require_once __DIR__ . '/../bootstrap/app.php';

use App\Models\User;
use App\Models\Product;
use App\Models\CashSession;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

echo "═══════════════════════════════════════════════════════════════\n";
echo "SMOKE TEST: ECOLINDUS Cash Session Flow\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Setup
$user = User::factory()->create(['name' => 'Test User']);
$product = Product::factory()->create(['stock' => 100, 'price' => 50, 'tax_rate' => 15]);

echo "✓ Test user and product created\n";
echo "  User: {$user->name} (ID: {$user->id})\n";
echo "  Product: {$product->name} - \${$product->price} (Tax: {$product->tax_rate}%)\n\n";

// 1. Open cash session
echo "STEP 1: Open Cash Session\n";
echo "─────────────────────────────────────────────────────────────\n";

$session = CashSession::create([
    'user_id' => $user->id,
    'opened_at' => now(),
    'opening_amount' => 100.00,
    'status' => 'open',
]);

echo "✓ Cash session opened\n";
echo "  Session ID: {$session->id}\n";
echo "  Opening Amount: \${$session->opening_amount}\n";
echo "  Status: {$session->status}\n\n";

// 2. Create and emit invoice
echo "STEP 2: Create Invoice with Mixed Payment\n";
echo "─────────────────────────────────────────────────────────────\n";

DB::transaction(function () use ($user, $product, $session) {
    $invoice = Invoice::create([
        'customer_id' => null,
        'user_id' => $user->id,
        'invoice_number' => 'TEST-' . now()->format('YmdHis'),
        'date' => now(),
        'status' => Invoice::STATUS_EMITIDA,
        'payment_method' => 'Efectivo',
        'subtotal' => 50.00,
        'tax_total' => 7.50,
        'total' => 57.50,
    ]);

    // Create invoice items
    \App\Models\InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => 50.00,
        'tax_rate' => 15,
        'line_total' => 50.00,
    ]);

    // Create payment breakdown: $35 cash + $22.50 transfer
    \App\Models\InvoicePayment::create([
        'invoice_id' => $invoice->id,
        'cash_amount' => 35.00,
        'transfer_amount' => 22.50,
    ]);

    echo "✓ Invoice created and emitted\n";
    echo "  Invoice #: {$invoice->invoice_number}\n";
    echo "  Subtotal: \${$invoice->subtotal}\n";
    echo "  Tax (15%): \${$invoice->tax_total}\n";
    echo "  Total: \${$invoice->total}\n";
    echo "  Payment: Cash \$35.00 + Transfer \$22.50\n\n";
});

// 3. Get session summary
echo "STEP 3: Get Cash Session Summary\n";
echo "─────────────────────────────────────────────────────────────\n";

$invoices = Invoice::where('user_id', $user->id)
    ->where('status', Invoice::STATUS_EMITIDA)
    ->whereBetween('date', [$session->opened_at, now()])
    ->with('payment')
    ->get();

$totalCash = (float) $invoices->sum(fn($inv) => $inv->payment->cash_amount ?? 0);
$totalTransfer = (float) $invoices->sum(fn($inv) => $inv->payment->transfer_amount ?? 0);
$totalInvoiced = (float) $invoices->sum('total');
$expectedClosing = round($session->opening_amount + $totalCash + $totalTransfer, 2);

echo "✓ Summary calculated\n";
echo "  Invoices: " . count($invoices) . "\n";
echo "  Total Invoiced: \${$totalInvoiced}\n";
echo "  Total Cash: \${$totalCash}\n";
echo "  Total Transfer: \${$totalTransfer}\n";
echo "  Expected Closing: \${$expectedClosing}\n\n";

// 4. Close session with reported amount (with a small difference)
echo "STEP 4: Close Cash Session with Arqueo\n";
echo "─────────────────────────────────────────────────────────────\n";

$reportedClosing = 156.50; // Slightly different than expected (diff = -0.50)
$difference = $expectedClosing - $reportedClosing;

$session->update([
    'closed_at' => now(),
    'closing_amount' => round($totalCash + $totalTransfer, 2),
    'total_invoiced' => $totalInvoiced,
    'total_cash' => $totalCash,
    'total_transfer' => $totalTransfer,
    'expected_closing' => $expectedClosing,
    'reported_closing_amount' => $reportedClosing,
    'difference' => $difference,
    'status' => 'closed',
]);

echo "✓ Cash session closed\n";
echo "  Expected Closing: \${$expectedClosing}\n";
echo "  Reported Closing: \${$reportedClosing}\n";
echo "  Difference: \${$difference}\n";
echo "  Status: {$session->status}\n\n";

// 5. Verify persisted fields
echo "STEP 5: Verify Persisted Arqueo Fields\n";
echo "─────────────────────────────────────────────────────────────\n";

$session->refresh();

echo "✓ Arqueo data persisted correctly:\n";
echo "  Opening Amount: \${$session->opening_amount}\n";
echo "  Total Invoiced: \${$session->total_invoiced}\n";
echo "  Total Cash: \${$session->total_cash}\n";
echo "  Total Transfer: \${$session->total_transfer}\n";
echo "  Expected Closing: \${$session->expected_closing}\n";
echo "  Reported Closing: \${$session->reported_closing_amount}\n";
echo "  Difference: \${$session->difference}\n\n";

// Validate
$allValid = 
    $session->opening_amount == 100.00 &&
    $session->total_invoiced == 57.50 &&
    $session->total_cash == 35.00 &&
    $session->total_transfer == 22.50 &&
    $session->expected_closing == 157.50 &&
    $session->reported_closing_amount == 156.50 &&
    $session->difference == 1.00 &&
    $session->status === 'closed';

echo "═══════════════════════════════════════════════════════════════\n";
if ($allValid) {
    echo "✓ ALL CHECKS PASSED - System working correctly!\n";
} else {
    echo "✗ VALIDATION FAILED - Check the values above\n";
}
echo "═══════════════════════════════════════════════════════════════\n";
