<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_reopen_annulled_invoice()
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['stock' => 10, 'price' => 5]);

        // Crear factura anulada
        $invoice = Invoice::factory()->create([
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'status' => Invoice::STATUS_ANULADA,
        ]);

        $this->actingAs($user)
            ->post(route('invoices.reopen', $invoice))
            ->assertRedirect(route('invoices.show', $invoice));

        $invoice->refresh();
        $this->assertEquals(Invoice::STATUS_EMITIDA, $invoice->status);
        $this->assertNull($invoice->cancelled_by);
        $this->assertNull($invoice->cancelled_at);
    }

    public function test_print_invoice_returns_pdf()
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['stock' => 10, 'price' => 5]);
        $invoice = Invoice::factory()->create([
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'status' => Invoice::STATUS_EMITIDA,
        ]);
        $invoice->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 5,
            'tax_rate' => 15,
            'line_total' => 10,
        ]);

        $this->actingAs($user)
            ->get(route('invoices.print', $invoice))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
