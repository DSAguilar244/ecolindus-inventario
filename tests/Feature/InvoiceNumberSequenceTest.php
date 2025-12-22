<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InvoiceNumberSequenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_sequential_invoice_numbers()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create(['id' => 1]);
        $this->seed(\Database\Seeders\CashSessionTestSeeder::class);
        $product = Product::factory()->create(['stock' => 100, 'price' => 10]);

        // Ensure invoice_numbers row exists
        DB::table('invoice_numbers')->insert(['prefix' => 'INV', 'last_number' => 0, 'created_at' => now(), 'updated_at' => now()]);

        $payloadTemplate = [
            'emit' => '1',
            'customer' => [
                'id_type' => '04',
                'identification' => null, // to be set
                'first_name' => 'Test',
                'last_name' => 'User',
                'address' => 'Test Address',
            ],
            'items' => [
                [
                    'product_id' => null, // to be set
                    'quantity' => 1,
                    'unit_price' => 10,
                    'tax_rate' => 15,
                ],
            ],
        ];

        $invoiceNumbers = [];

        for ($i = 1; $i <= 3; $i++) {
            $payload = $payloadTemplate;
            // Ensure a valid 10-digit identification for Ecuadorian validation in tests
            $payload['customer']['identification'] = sprintf('%010d', 100000000 + $i);
            $payload['items'][0]['product_id'] = $product->id;

            $this->actingAs($user)
                ->post(route('invoices.store'), $payload)
                ->assertRedirect();

            $invoice = DB::table('invoices')->latest('id')->first();
            $this->assertNotNull($invoice);
            $invoiceNumbers[] = $invoice->invoice_number;
        }

        // Assert the invoice_numbers are sequential and properly formatted
        $this->assertCount(3, $invoiceNumbers);
        $this->assertEquals('INV-000001', $invoiceNumbers[0]);
        $this->assertEquals('INV-000002', $invoiceNumbers[1]);
        $this->assertEquals('INV-000003', $invoiceNumbers[2]);

        // Check the invoice_numbers table was updated
        $row = DB::table('invoice_numbers')->first();
        $this->assertEquals(3, $row->last_number);
    }
}
