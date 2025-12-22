<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceTaxZeroTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_preserves_zero_tax()
    {
        $user = User::factory()->create(['id' => 1]);
        // Ensure open cash session for the test user
        $this->seed(\Database\Seeders\CashSessionTestSeeder::class);
        $this->actingAs($user);

        $product = Product::factory()->create([ 'price' => 10, 'tax_rate' => 15 ]);
        $customer = Customer::factory()->create();

        $response = $this->post(route('invoices.store'), [
            'customer_id' => $customer->id,
            'emit' => '1',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_price' => 10,
                    'tax_rate' => 0,
                ],
            ],
        ]);

        $response->assertRedirect();

        $invoice = Invoice::first();
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'product_id' => $product->id,
            'tax_rate' => 0,
        ]);

        // invoice totals: subtotal = 10, tax_total = 0, total = 10
        $invoice->refresh();
        $this->assertEquals(0, (float) $invoice->tax_total);
        $this->assertEquals(10.00, (float) $invoice->subtotal);
        $this->assertEquals(10.00, (float) $invoice->total);
    }

    public function test_update_preserves_zero_tax()
    {
        $user = User::factory()->create(['id' => 1]);
        // Ensure open cash session for the test user
        $this->seed(\Database\Seeders\CashSessionTestSeeder::class);
        $this->actingAs($user);

        $product = Product::factory()->create([ 'price' => 10, 'tax_rate' => 15 ]);
        $customer = Customer::factory()->create();

        // create invoice initially with default tax 15% for item - as PENDING so we can update
        $response = $this->post(route('invoices.store'), [
            'customer_id' => $customer->id,
            'emit' => '0',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_price' => 10,
                    'tax_rate' => 15,
                ],
            ],
        ]);

        $invoice = Invoice::first();
        $this->assertDatabaseHas('invoice_items', [ 'invoice_id' => $invoice->id, 'product_id' => $product->id, 'tax_rate' => 15 ]);

        // Now update invoice to set tax to 0 and emit
        $response = $this->put(route('invoices.update', $invoice), [
            'customer_id' => $customer->id,
            'emit' => '1',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_price' => 10,
                    'tax_rate' => 0,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('invoice_items', [ 'invoice_id' => $invoice->id, 'product_id' => $product->id, 'tax_rate' => 0 ]);

        $invoice->refresh();
        $this->assertEquals(0, (float) $invoice->tax_total);
        $this->assertEquals(10.00, (float) $invoice->total);
    }
}
