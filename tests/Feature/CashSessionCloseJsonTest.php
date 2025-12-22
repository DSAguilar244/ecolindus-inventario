<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use App\Models\CashSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashSessionCloseJsonTest extends TestCase
{
    use RefreshDatabase;

    public function test_close_returns_json_with_expected_fields_and_saves_notes()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Open a cash session
        $this->post(route('cash_sessions.open'), ['opening_amount' => 50]);
        $session = CashSession::where('user_id', $user->id)->where('status', 'open')->first();
        $this->assertNotNull($session);

        // Create a product and an emitted invoice with payment that sums to 20
        $product = Product::factory()->create(['stock' => 10, 'price' => 20]);

        $payload = [
            'emit' => '1',
            'customer' => [
                'id_type' => '04',
                'identification' => '1234567890',
                'first_name' => 'Json',
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

        // Close the session via JSON and include a note
        $reported = 70.00; // opening 50 + invoice 20
        $note = 'Diferencia justificada: cambio';

        $res = $this->postJson(route('cash_sessions.close'), [
            'reported_closing_amount' => $reported,
            'notes' => $note,
        ]);

        $res->assertStatus(200)->assertJson([ 'success' => true ]);
        $json = $res->json();

        $this->assertArrayHasKey('expected_closing', $json);
        $this->assertArrayHasKey('reported_closing_amount', $json);
        $this->assertArrayHasKey('difference', $json);

        $session->refresh();
        $this->assertStringContainsString('Nota:', $session->notes);
        $this->assertStringContainsString('Diferencia justificada', $session->notes);
    }

    public function test_close_fails_when_difference_and_no_notes_json()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Open a cash session
        $this->post(route('cash_sessions.open'), ['opening_amount' => 100]);
        $session = CashSession::where('user_id', $user->id)->where('status', 'open')->first();
        $this->assertNotNull($session);

        // Create a product and an emitted invoice with payment of 10
        $product = Product::factory()->create(['stock' => 10, 'price' => 10]);

        $payload = [
            'emit' => '1',
            'customer' => [
                'id_type' => '04',
                'identification' => '2234567890',
                'first_name' => 'Json',
                'last_name' => 'Buyer',
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
            'cash_amount' => 10,
            'transfer_amount' => 0,
        ];

        $this->post(route('invoices.store'), $payload)->assertRedirect();

        // Reported closing that causes a difference, but do not send notes
        $reported = 50.00; // opening 100 + invoice 10 = expected 110, reported 50 -> difference

        $res = $this->postJson(route('cash_sessions.close'), [
            'reported_closing_amount' => $reported,
        ]);

        $res->assertStatus(422);
        $res->assertJsonFragment(['message' => 'Por favor proporciona una nota explicativa para la diferencia']);

        // session must still be open
        $session->refresh();
        $this->assertEquals('open', $session->status);
    }
}
