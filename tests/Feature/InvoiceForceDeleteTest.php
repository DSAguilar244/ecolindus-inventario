<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceForceDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_force_delete_invoice()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10, 'stock' => 50, 'tax_rate' => 0]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $item = InvoiceItem::create(['invoice_id' => $invoice->id, 'product_id' => $product->id, 'quantity' => 1, 'unit_price' => 10, 'line_total' => 10]);

        $res = $this->actingAs($user)->delete(route('invoices.forceDestroy', $invoice));
        $res->assertRedirect();
        $res->assertSessionHas('error');

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id]);
        $this->assertDatabaseHas('invoice_items', ['id' => $item->id]);
    }

    public function test_admin_can_force_delete_invoice_and_items()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $product = Product::factory()->create(['price' => 10, 'stock' => 50, 'tax_rate' => 0]);
        $invoice = Invoice::factory()->create(['user_id' => $admin->id]);
        $item = InvoiceItem::create(['invoice_id' => $invoice->id, 'product_id' => $product->id, 'quantity' => 1, 'unit_price' => 10, 'line_total' => 10]);

        // Simulate that the invoice was emitted and stock was decreased accordingly
        $product->decrement('stock', $item->quantity);

        $res = $this->actingAs($admin)->delete(route('invoices.forceDestroy', $invoice), ['audit_reason' => 'Prueba eliminación admin']);
        $res->assertRedirect(route('invoices.index'));
        $res->assertSessionHas('success');

        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
        $this->assertDatabaseMissing('invoice_items', ['id' => $item->id]);
        
        $product->refresh();
        // stock was initially 50, decreased to 49 when we emitted, should be restored to 50 after force delete
        $this->assertEquals(50, $product->stock);

        // Audit log created
        $this->assertDatabaseHas('audit_logs', [
            'invoice_id' => $invoice->id,
            'user_id' => $admin->id,
            'action' => 'delete',
            'reason' => 'Prueba eliminación admin'
        ]);
    }
}
