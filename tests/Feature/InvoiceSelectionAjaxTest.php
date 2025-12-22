<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceSelectionAjaxTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_invoice_item_using_ajax_product_search()
    {
        $user = User::factory()->create(['id' => 1]);
        $this->seed(\Database\Seeders\CashSessionTestSeeder::class);
        $product = Product::factory()->create(['name' => 'Agua Pura', 'code' => 'AP-01', 'price' => 10, 'tax_rate' => 0]);

        // Verify the search endpoint returns the product
        $resp = $this->actingAs($user)->getJson(route('products.search', ['q' => 'AP-01']));
        $resp->assertOk();
        $json = $resp->json();
        $this->assertCount(1, $json['results']);
        $pid = $json['results'][0]['id'];

        // Build payload as if the form was submitted after selecting via AJAX
        $payload = [
            'emit' => '1',
            'customer' => [
                'id_type' => '04',
                'identification' => '1999999999',
                'first_name' => 'Cliente Ajax',
                'last_name' => 'Testing',
                'address' => 'Calle Ajax 1',
            ],
            'items' => [
                [
                    'product_id' => $pid,
                    'quantity' => 2,
                    'unit_price' => $product->price,
                    'tax_rate' => 0,
                ],
            ],
        ];

        $post = $this->actingAs($user)->post(route('invoices.store'), $payload);
        $post->assertRedirect();

        $this->assertDatabaseHas('invoices', ['user_id' => $user->id]);
        $this->assertDatabaseHas('invoice_items', ['product_id' => $product->id, 'quantity' => 2]);
    }
}
