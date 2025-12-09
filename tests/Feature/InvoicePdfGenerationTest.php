<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePdfGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_pdf_print_returns_pdf_and_contains_invoice_number()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10, 'stock' => 10, 'tax_rate' => 0]);
        $customer = \App\Models\Customer::factory()->create();
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'customer_id' => $customer->id]);
        $item = InvoiceItem::create(['invoice_id' => $invoice->id, 'product_id' => $product->id, 'quantity' => 2, 'unit_price' => 10, 'line_total' => 20]);

        $res = $this->actingAs($user)->get(route('invoices.print', $invoice));
        $res->assertStatus(200);
        $res->assertHeader('Content-Type', 'application/pdf');
        $res->assertSee($invoice->invoice_number);
    }
}
