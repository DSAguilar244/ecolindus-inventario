<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceEditButtonVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_does_not_see_edit_button_for_emitted_invoice()
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();
        $invoice = Invoice::factory()->create(['status' => Invoice::STATUS_EMITIDA, 'customer_id' => $customer->id]);

        $res = $this->actingAs($user)->get(route('invoices.show', $invoice));
        $res->assertStatus(200);
        $res->assertDontSee('Editar (admin)');
    }

    public function test_admin_sees_edit_button_for_emitted_invoice()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = Customer::factory()->create();
        $invoice = Invoice::factory()->create(['status' => Invoice::STATUS_EMITIDA, 'customer_id' => $customer->id]);

        $res = $this->actingAs($admin)->get(route('invoices.show', $invoice));
        $res->assertStatus(200);
        $res->assertSee('Editar (admin)');
    }

    public function test_admin_sees_edit_button_in_index_for_emitted_invoice()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = Customer::factory()->create();
        $invoice = Invoice::factory()->create(['status' => Invoice::STATUS_EMITIDA, 'customer_id' => $customer->id]);

        $res = $this->actingAs($admin)->get(route('invoices.index'));
        $res->assertStatus(200);
        $res->assertSee('Editar (admin)');
    }
}
