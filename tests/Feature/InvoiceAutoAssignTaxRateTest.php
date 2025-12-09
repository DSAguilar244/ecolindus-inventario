<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceAutoAssignTaxRateTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_assigns_product_tax_rate_when_missing()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // product has tax_rate 15%
        $product = Product::factory()->create([ 'price' => 10, 'tax_rate' => 15 ]);
        $customer = Customer::factory()->create();

        // Submit invoice items WITHOUT tax_rate key (simulate user not overriding it)
        $response = $this->post(route('invoices.store'), [
            'customer_id' => $customer->id,
            'emit' => '1',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_price' => 10,
                    // no 'tax_rate' provided here
                ],
            ],
        ]);

        $response->assertRedirect();

        $invoice = Invoice::first();

        // Invoice item should have tax_rate taken from product (15)
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'product_id' => $product->id,
            'tax_rate' => 15,
        ]);

        // Totals: subtotal = 10, tax_total = 1.5, total = 11.5
        $invoice->refresh();
        $this->assertEquals(1.5, (float) $invoice->tax_total);
        $this->assertEquals(10.00, (float) $invoice->subtotal);
        $this->assertEquals(11.5, (float) $invoice->total);
    }
}
