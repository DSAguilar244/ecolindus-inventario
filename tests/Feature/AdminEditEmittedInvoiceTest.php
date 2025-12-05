<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEditEmittedInvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_edit_emitted_invoice()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10, 'stock' => 10, 'tax' => 0]);

        // Create an emitted invoice with an item
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'status' => Invoice::STATUS_EMITIDA]);
        $item = InvoiceItem::create(['invoice_id' => $invoice->id, 'product_id' => $product->id, 'quantity' => 2, 'unit_price' => 10, 'line_total' => 20]);

        // Simulate decrement of stock for initial emission
        $product->decrement('stock', 2);

        $res = $this->actingAs($user)->get(route('invoices.edit', $invoice));
        $res->assertRedirect();
        $res->assertSessionHas('error');
    }

    public function test_admin_can_edit_emitted_invoice_and_stock_adjusts()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $product = Product::factory()->create(['price' => 10, 'stock' => 10, 'tax' => 0]);

        // Create an emitted invoice with an item for quantity 2
        $customer = \App\Models\Customer::factory()->create();
        $invoice = Invoice::factory()->create(['user_id' => $admin->id, 'status' => Invoice::STATUS_EMITIDA, 'customer_id' => $customer->id]);
        $item = InvoiceItem::create(['invoice_id' => $invoice->id, 'product_id' => $product->id, 'quantity' => 2, 'unit_price' => 10, 'line_total' => 20]);

        // Simulate decrement of stock for initial emission
        $product->decrement('stock', 2);

        // Admin increases quantity to 4
        $payload = [
            'customer_id' => $invoice->customer_id,
            'emit' => '1',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 4, 'unit_price' => 10, 'tax_rate' => 0],
            ],
            'audit_reason' => 'Ajuste por conteo inventario'
        ];

        // Ensure the session has a CSRF token by visiting the edit page first
        $this->actingAs($admin);
        $this->get(route('invoices.edit', $invoice));

        // Read the session token and include it explicitly in the PUT payload
        $token = $this->app['session']->token();
        $payload['_token'] = $token;

        $res = $this->put(route('invoices.update', $invoice), $payload);
        $res->assertRedirect(route('invoices.show', $invoice));
        $res->assertSessionHas('success');

        $product->refresh();
        // Initial stock 10, after first emission 8, then decreased by delta 2 -> 6
        $this->assertEquals(6, $product->stock);

        $this->assertDatabaseHas('audit_logs', [
            'invoice_id' => $invoice->id,
            'user_id' => $admin->id,
            'action' => 'edit',
            'reason' => 'Ajuste por conteo inventario'
        ]);

        // Now admin decreases quantity to 1, which should restore 3 units
        $payload['items'][0]['quantity'] = 1;
        $res = $this->actingAs($admin)->put(route('invoices.update', $invoice), $payload);
        $res->assertRedirect(route('invoices.show', $invoice));

        $product->refresh();
        // stock becomes 6 + 3 = 9
        $this->assertEquals(9, $product->stock);
    }
}
