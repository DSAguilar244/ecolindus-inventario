<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use App\Models\CashSession;
use App\Models\IdempotencyKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdempotencyKeyTest extends TestCase
{
    use RefreshDatabase;

    public function test_close_with_new_idempotency_key_succeeds()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Open a cash session
        $this->post(route('cash_sessions.open'), ['opening_amount' => 50]);
        $session = CashSession::where('user_id', $user->id)->where('status', 'open')->first();
        $this->assertNotNull($session);

        // Create and emit an invoice with payment
        $product = Product::factory()->create(['stock' => 10, 'price' => 20]);

        $payload = [
            'emit' => '1',
            'customer' => [
                'id_type' => '04',
                'identification' => '1234567890',
                'first_name' => 'Test',
                'last_name' => 'Buyer',
            ],
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_price' => 20,
                    'tax_rate' => 0,
                ],
            ],
            'payment_method' => 'Pago físico',
            'cash_amount' => 20,
            'transfer_amount' => 0,
        ];

        $this->post(route('invoices.store'), $payload)->assertRedirect();

        // Close session with idempotency key
        $idempotencyKey = 'cs_close_' . time() . '_unique123';
        $reported = 70.00;
        $note = 'Test note for idempotency';

        $res = $this->postJson(route('cash_sessions.close'), [
            'reported_closing_amount' => $reported,
            'notes' => $note,
            'idempotency_key' => $idempotencyKey,
        ]);

        $res->assertStatus(200)->assertJson(['success' => true]);

        // Verify idempotency key was stored
        $this->assertDatabaseHas('idempotency_keys', [
            'key' => $idempotencyKey,
            'cash_session_id' => $session->id,
        ]);

        // Session should be closed
        $session->refresh();
        $this->assertEquals('closed', $session->status);
    }

    public function test_close_with_duplicate_idempotency_key_fails()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Open a cash session
        $this->post(route('cash_sessions.open'), ['opening_amount' => 50]);
        $session = CashSession::where('user_id', $user->id)->where('status', 'open')->first();
        $this->assertNotNull($session);

        // Create and emit an invoice with payment
        $product = Product::factory()->create(['stock' => 10, 'price' => 20]);

        $payload = [
            'emit' => '1',
            'customer' => [
                'id_type' => '04',
                'identification' => '1234567890',
                'first_name' => 'Test',
                'last_name' => 'Buyer',
            ],
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_price' => 20,
                    'tax_rate' => 0,
                ],
            ],
            'payment_method' => 'Pago físico',
            'cash_amount' => 20,
            'transfer_amount' => 0,
        ];

        $this->post(route('invoices.store'), $payload)->assertRedirect();

        // First close with idempotency key should succeed
        $idempotencyKey = 'cs_close_' . time() . '_duplicate123';
        $reported = 70.00;
        $note = 'First close attempt';

        $res1 = $this->postJson(route('cash_sessions.close'), [
            'reported_closing_amount' => $reported,
            'notes' => $note,
            'idempotency_key' => $idempotencyKey,
        ]);

        $res1->assertStatus(200)->assertJson(['success' => true]);

        // Verify key was stored
        $this->assertDatabaseHas('idempotency_keys', ['key' => $idempotencyKey]);

        // Now open a new session for the duplicate attempt test
        // (since the first session is closed, we can't close it again)
        $this->post(route('cash_sessions.open'), ['opening_amount' => 100]);
        $session2 = CashSession::where('user_id', $user->id)->where('status', 'open')->first();
        $this->assertNotNull($session2);

        // Create and emit another invoice
        $product2 = Product::factory()->create(['stock' => 10, 'price' => 10]);
        $payload2 = [
            'emit' => '1',
            'customer' => [
                'id_type' => '04',
                'identification' => '2234567890',
                'first_name' => 'Test2',
                'last_name' => 'Buyer2',
            ],
            'items' => [
                [
                    'product_id' => $product2->id,
                    'quantity' => 1,
                    'unit_price' => 10,
                    'tax_rate' => 0,
                ],
            ],
            'payment_method' => 'Pago físico',
            'cash_amount' => 10,
            'transfer_amount' => 0,
        ];

        $this->post(route('invoices.store'), $payload2)->assertRedirect();

        // Attempt to close with the SAME idempotency key should fail with 409
        $res2 = $this->postJson(route('cash_sessions.close'), [
            'reported_closing_amount' => 110.00,
            'notes' => 'Second close with duplicate key',
            'idempotency_key' => $idempotencyKey,
        ]);

        $res2->assertStatus(409)->assertJson(['message' => 'Duplicate request']);

        // Session2 should still be open
        $session2->refresh();
        $this->assertEquals('open', $session2->status);
    }
}
