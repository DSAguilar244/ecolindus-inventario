<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceIgnoresEmptyRowsTest extends TestCase
{
    use RefreshDatabase;

    public function test_ignores_empty_item_rows_and_saves_single_item()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10, 'tax_rate' => 0, 'stock' => 100]);

        $payload = [
            'emit' => '1',
            'customer' => [
                'id_type' => '04',
                'identification' => '1100000001',
                'first_name' => 'Single',
                'last_name' => 'Item',
                'address' => 'Calle 1',
            ],
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_price' => $product->price,
                    'tax_rate' => 0,
                ],
                // empty row that sometimes gets rendered by the client
                [
                    'product_id' => '',
                    'quantity' => '',
                    'unit_price' => '',
                    'tax_rate' => '',
                ],
            ],
        ];

        $res = $this->actingAs($user)->post(route('invoices.store'), $payload);
        $res->assertRedirect();

        $this->assertDatabaseHas('invoice_items', ['product_id' => $product->id, 'quantity' => 2]);
        $this->assertDatabaseCount('invoice_items', 1);
    }
}
