<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Rules\PaymentTotalMatchesInvoice;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentTotalMatchesInvoiceMultipleItemsTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Product::factory()->create([ 'id' => 10, 'price' => 20.00, 'tax_rate' => config('taxes.iva') ]); // IVA 15% on this
        Product::factory()->create([ 'id' => 11, 'price' => 5.00, 'tax_rate' => 0 ]); // zero tax
    }

    public function test_multiple_items_mixed_taxes_valid()
    {
        // Item 1: 2 units of product 10 (20.00) => line 40.00, tax 15% => 6.00
        // Item 2: 3 units of product 11 (5.00) => line 15.00, tax 0% => 0
        // Total expected = 40+15 + 6 = 61.00
        $req = Request::create('/dummy', 'POST', [
            'items' => [
                ['product_id' => 10, 'quantity' => 2, 'unit_price' => 20.00, 'tax_rate' => config('taxes.iva')],
                ['product_id' => 11, 'quantity' => 3, 'unit_price' => 5.00, 'tax_rate' => 0],
            ],
            'cash_amount' => 61.00,
            'transfer_amount' => 0,
        ]);

        $rule = new PaymentTotalMatchesInvoice($req);
        $this->assertTrue($rule->passes('cash_amount', 61.00));
    }

    public function test_multiple_items_mixed_taxes_invalid()
    {
        $req = Request::create('/dummy', 'POST', [
            'items' => [
                ['product_id' => 10, 'quantity' => 2, 'unit_price' => 20.00, 'tax_rate' => config('taxes.iva')],
                ['product_id' => 11, 'quantity' => 3, 'unit_price' => 5.00, 'tax_rate' => 0],
            ],
            'cash_amount' => 60.00,
            'transfer_amount' => 0,
        ]);

        $rule = new PaymentTotalMatchesInvoice($req);
        $this->assertFalse($rule->passes('cash_amount', 60.00));
    }
}
