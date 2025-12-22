<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceStatusFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_filter_invoices_by_status()
    {
        $user = User::factory()->create();

        // Create invoices with different statuses
        $inv1 = Invoice::create(['customer_id' => null, 'user_id' => $user->id, 'invoice_number' => 'INV-001', 'date' => now(), 'status' => Invoice::STATUS_PENDIENTE, 'subtotal' => 0, 'tax_total' => 0, 'total' => 0]);
        $inv2 = Invoice::create(['customer_id' => null, 'user_id' => $user->id, 'invoice_number' => 'INV-002', 'date' => now(), 'status' => Invoice::STATUS_EMITIDA, 'subtotal' => 0, 'tax_total' => 0, 'total' => 0]);
        $inv3 = Invoice::create(['customer_id' => null, 'user_id' => $user->id, 'invoice_number' => 'INV-003', 'date' => now(), 'status' => Invoice::STATUS_ANULADA, 'subtotal' => 0, 'tax_total' => 0, 'total' => 0]);

        $this->actingAs($user)
            ->get(route('invoices.index', ['status' => 'pendiente']))
            ->assertOk()
            ->assertSee('INV-001')
            ->assertDontSee('INV-002')
            ->assertDontSee('INV-003');
    }
}
