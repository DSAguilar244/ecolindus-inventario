<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePendingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_pending_then_emit_invoice_changes_status_and_stock()
    {
        $user = User::factory()->create();

        $product = Product::factory()->create(['stock' => 10, 'price' => 5]);

        $payload = [
            'customer' => [
                'id_type' => '04',
                'identification' => '1112223334',
                'first_name' => 'Pendiente',
                'last_name' => 'Cliente',
            ],
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_price' => 5,
                    'tax_rate' => 15,
                ],
            ],
            'emit' => 0,
        ];

        // Create as pending
        $this->actingAs($user)
            ->post(route('invoices.store'), $payload)
            ->assertRedirect();

        $invoice = Invoice::first();
        $this->assertNotNull($invoice);
        $this->assertEquals(Invoice::STATUS_PENDIENTE, $invoice->status);

        // Stock should not have changed
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 10]);

        // Now emit via update
        $updatePayload = [
            'customer' => $payload['customer'],
            'items' => $payload['items'],
            'emit' => 1,
        ];

        $this->actingAs($user)
            ->put(route('invoices.update', $invoice), $updatePayload)
            ->assertRedirect();

        $invoice->refresh();
        $this->assertEquals(Invoice::STATUS_EMITIDA, $invoice->status);

        // Stock should be decremented by 2
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 8]);
    }
}
