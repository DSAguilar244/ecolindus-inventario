<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceMergeDuplicateItemsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_merges_duplicate_items_for_same_product()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 15, 'tax' => 0, 'stock' => 50]);

        $payload = [
            'emit' => '1',
            'customer' => [
                'id_type' => '04',
                'identification' => '1100000002',
                'first_name' => 'Merging',
                'last_name' => 'Test',
                'address' => 'Calle Merge 1',
            ],
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_price' => $product->price,
                    'tax_rate' => 0,
                ],
                [
                    'product_id' => $product->id,
                    'quantity' => 3,
                    'unit_price' => $product->price,
                    'tax_rate' => 0,
                ],
            ],
        ];

        $res = $this->actingAs($user)->post(route('invoices.store'), $payload);
        $res->assertRedirect();

        $this->assertDatabaseHas('invoice_items', ['product_id' => $product->id, 'quantity' => 5]);
        $this->assertDatabaseCount('invoice_items', 1);

        // stock should be decremented by total merged quantity (5)
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 45]);
    }

    public function test_update_merges_duplicate_items_for_same_product()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 15, 'tax' => 0, 'stock' => 50]);

        // Create a pending invoice we can update
        $invoice = \App\Models\Invoice::factory()->create(['user_id' => $user->id, 'status' => \App\Models\Invoice::STATUS_PENDIENTE]);

        $payload = [
            'emit' => '0',
            'customer' => [
                'id_type' => '04',
                'identification' => '1133333333',
                'first_name' => 'Update',
                'last_name' => 'Merge',
                'address' => 'Calle Merge 2',
            ],
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_price' => $product->price,
                    'tax_rate' => 0,
                ],
                [
                    'product_id' => $product->id,
                    'quantity' => 4,
                    'unit_price' => $product->price,
                    'tax_rate' => 0,
                ],
            ],
        ];

        $res = $this->actingAs($user)->put(route('invoices.update', $invoice), $payload);
        $res->assertRedirect();

        $this->assertDatabaseHas('invoice_items', ['product_id' => $product->id, 'quantity' => 5]);
        $this->assertDatabaseCount('invoice_items', 1);
    }
}
