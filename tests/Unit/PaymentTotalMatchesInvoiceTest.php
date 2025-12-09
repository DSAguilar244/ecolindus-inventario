<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Rules\PaymentTotalMatchesInvoice;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentTotalMatchesInvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        // create a sample product used by tests
        Product::factory()->create([ 'id' => 1, 'price' => 10.00, 'tax_rate' => config('taxes.iva') ]);
        Product::factory()->create([ 'id' => 2, 'price' => 5.00, 'tax_rate' => 0 ]);
    }

    public function test_valid_exact_match_cash_only()
    {
        $req = Request::create('/dummy', 'POST', [
            'items' => [ [ 'product_id' => 1, 'quantity' => 1, 'unit_price' => 10.00, 'tax_rate' => config('taxes.iva') ] ],
            'cash_amount' => 11.50, // price 10 + tax (IVA 15%) => 11.50
            'transfer_amount' => 0,
        ]);

        $rule = new PaymentTotalMatchesInvoice($req);
        $this->assertTrue($rule->passes('cash_amount', 11.00));
    }

    public function test_valid_exact_match_transfer_only()
    {
        $req = Request::create('/dummy', 'POST', [
            'items' => [ [ 'product_id' => 2, 'quantity' => 2, 'unit_price' => 5.00, 'tax_rate' => 0 ] ],
            'cash_amount' => 0,
            'transfer_amount' => 10.00,
        ]);

        $rule = new PaymentTotalMatchesInvoice($req);
        $this->assertTrue($rule->passes('transfer_amount', 10.00));
    }

    public function test_invalid_mismatch_sum()
    {
        $req = Request::create('/dummy', 'POST', [
            'items' => [ [ 'product_id' => 1, 'quantity' => 1, 'unit_price' => 10.00, 'tax_rate' => config('taxes.iva') ] ],
            'cash_amount' => 5.00,
            'transfer_amount' => 5.00,
        ]);

        $rule = new PaymentTotalMatchesInvoice($req);
        $this->assertFalse($rule->passes('cash_amount', 5.00));
    }

    public function test_tolerance_two_decimals()
    {
        // Purchase total should be 11.50, accept values that round to 11.50
        $req = Request::create('/dummy', 'POST', [
            'items' => [ [ 'product_id' => 1, 'quantity' => 1, 'unit_price' => 10.00, 'tax_rate' => config('taxes.iva') ] ],
            'cash_amount' => 11.495,
            'transfer_amount' => 0.005,
        ]);

        $rule = new PaymentTotalMatchesInvoice($req);
        $this->assertTrue($rule->passes('cash_amount', 10.995));
    }
}
