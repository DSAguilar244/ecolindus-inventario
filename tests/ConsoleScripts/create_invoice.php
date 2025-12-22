<?php

// This script boots Laravel and creates an invoice with a single item.
// Usage: php create_invoice.php IDENTIFICATION PRODUCT_ID

require __DIR__.'/../../vendor/autoload.php';

$app = require_once __DIR__.'/../../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

$identification = $argv[1] ?? 'concurrent-'.uniqid();
$productId = $argv[2] ?? null;

if (! $productId) {
    fwrite(STDERR, "Product ID is required\n");
    exit(1);
}

$product = Product::find($productId);
if (! $product) {
    fwrite(STDERR, "Product not found: {$productId}\n");
    exit(1);
}

$customer = Customer::firstOrCreate([
    'identification' => $identification,
],
    [
        'first_name' => 'Concurrent',
        'last_name' => 'Client',
        'phone' => null,
        'email' => null,
        'address' => 'Testing Address',
    ]
);

try {
    DB::beginTransaction();
    $row = DB::table('invoice_numbers')->lockForUpdate()->first();
    $next = $row->last_number + 1;
    DB::table('invoice_numbers')->update(['last_number' => $next, 'updated_at' => now()]);
    $candidate = Str::upper(sprintf('%s-%s', $row->prefix ?? 'INV', str_pad($next, 6, '0', STR_PAD_LEFT)));

    $invoice = Invoice::create([
        'customer_id' => $customer->id,
        'user_id' => null,
        'invoice_number' => $candidate,
        'date' => now(),
        'status' => Invoice::STATUS_EMITIDA,
    ]);

    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => $product->price,
        'tax_rate' => 0,
        'line_total' => $product->price,
    ]);

    DB::commit();
    echo "CREATED: {$invoice->invoice_number}\n";
    exit(0);
} catch (Throwable $e) {
    DB::rollBack();
    fwrite(STDERR, 'ERROR: '.$e->getMessage()."\n");
    exit(1);
}
