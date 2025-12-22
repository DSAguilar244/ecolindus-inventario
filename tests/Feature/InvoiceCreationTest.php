<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_an_invoice_when_there_is_sufficient_stock()
    {
        $user = User::factory()->create(['id' => 1]);
        $product = Product::factory()->create(['stock' => 10, 'price' => 5]);

        // Ensure there is an open cash session in the testing environment
        $this->seed(\Database\Seeders\CashSessionTestSeeder::class);

        $payload = [
            'emit' => '1',
            'customer' => [
                'id_type' => '04',
                'identification' => '1234567890',
                'first_name' => 'Juan',
                'last_name' => 'Perez',
                'address' => 'Calle Principal 123',
            ],
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_price' => 5,
                    'tax_rate' => 15,
                ],
            ],
        ];

        $this->actingAs($user)
            ->post(route('invoices.store'), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('customers', ['identification' => '1234567890']);
        $this->assertDatabaseHas('invoices', ['user_id' => $user->id]);
        $this->assertDatabaseHas('invoice_items', ['product_id' => $product->id, 'quantity' => 2]);
    }

    public function test_rejects_invoice_creation_when_stock_is_insufficient()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 1, 'price' => 5]);

        $payload = [
            'emit' => '1',
            'customer' => [
                'id_type' => '04',
                'identification' => '9876543210',
                'first_name' => 'Ana',
                'last_name' => 'Lopez',
                'address' => 'Avenida Siempre Viva 742',
            ],
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'unit_price' => 5,
                    'tax_rate' => 15,
                ],
            ],
        ];

        $this->actingAs($user)
            ->post(route('invoices.store'), $payload)
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('invoices', ['user_id' => $user->id]);
    }

    public function test_rejects_invoice_creation_without_open_cash_session()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10, 'price' => 5]);

        $payload = [
            'emit' => '1',
            'customer' => [
                'id_type' => '04',
                'identification' => '1234567890',
                'first_name' => 'NoCaja',
                'last_name' => 'Test',
                'address' => 'Sin Caja',
            ],
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_price' => 5,
                    'tax_rate' => 0,
                ],
            ],
        ];

        // Ask for JSON to get the 403 response we return for API consumers
        $this->actingAs($user)
            ->postJson(route('invoices.store'), $payload)
            ->assertStatus(403);
    }
}
