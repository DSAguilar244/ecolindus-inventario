<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a sample customer
        $customer = Customer::firstOrCreate(
            ['id_type' => '04', 'identification' => '9999999999'],
            ['first_name' => 'Cliente', 'last_name' => 'Ejemplo', 'phone' => '0999999999', 'email' => 'cliente@example.com']
        );

        // Pick some products
        $products = Product::orderBy('id')->take(3)->get();
        if ($products->isEmpty()) {
            return; // nothing to seed against
        }

        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'user_id' => 1,
            'invoice_number' => Str::upper('INV-'.now()->format('YmdHis')),
            'date' => now(),
            'status' => 'emitida',
        ]);

        $subtotal = 0;
        $taxTotal = 0;

        foreach ($products as $p) {
            $qty = 1;
            $unit = $p->price ?? 10;
            $line = $qty * $unit;
            $tax = (15 / 100) * $line;

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $p->id,
                'quantity' => $qty,
                'unit_price' => $unit,
                'tax_rate' => 15,
                'line_total' => $line,
            ]);

            $subtotal += $line;
            $taxTotal += $tax;
        }

        $invoice->update([
            'subtotal' => $subtotal,
            'tax_total' => $taxTotal,
            'total' => $subtotal + $taxTotal,
        ]);
    }
}
