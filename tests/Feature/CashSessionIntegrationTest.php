<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Product;
use App\Models\User;
use App\Models\CashSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashSessionIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cash_session_records_payments_and_closes_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Open a cash session
        $this->post(route('cash_sessions.open'), ['opening_amount' => 100]);
        $session = CashSession::where('user_id', $user->id)->where('status', 'open')->first();
        $this->assertNotNull($session);

        // Create a product and an emitted invoice with mixed payment
        $product = Product::factory()->create(['stock' => 10, 'price' => 10]);

        $payload = [
            'emit' => '1',
            'customer' => [
                'id_type' => '04',
                'identification' => '1234567890',
                'first_name' => 'Test',
                'last_name' => 'Buyer',
                'address' => 'Address 1',
            ],
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_price' => 10,
                    'tax_rate' => 0,
                ],
            ],
            'payment_method' => 'Pago físico',
            'cash_amount' => 6,
            'transfer_amount' => 4,
        ];

        $this->post(route('invoices.store'), $payload)->assertRedirect();

        $invoice = Invoice::first();
        $this->assertDatabaseHas('invoice_payments', ['invoice_id' => $invoice->id, 'cash_amount' => 6.00, 'transfer_amount' => 4.00]);

        // Fetch summary and verify totals
        $res = $this->getJson(route('cash_sessions.summary'));
        $res->assertStatus(200);
        $json = $res->json();

        $this->assertEquals(1, $json['totals']['invoices_count']);
        $this->assertEquals(6.00, $json['totals']['total_cash']);
        $this->assertEquals(4.00, $json['totals']['total_transfer']);
        $this->assertEquals(10.00, $json['totals']['total_invoices']);

        // Close the session (confirming reported amount)
        $this->post(route('cash_sessions.close'), ['reported_closing_amount' => 10.00])->assertRedirect();

        $session->refresh();
        $this->assertEquals('closed', $session->status);
        $this->assertEquals(10.00, (float) $session->closing_amount);
    }
}
