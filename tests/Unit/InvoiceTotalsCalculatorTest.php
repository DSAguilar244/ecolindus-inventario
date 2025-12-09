<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\InvoiceTotalsCalculator;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceTotalsCalculatorTest extends TestCase
{
    use RefreshDatabase;

    protected InvoiceTotalsCalculator $calculator;

    public function setUp(): void
    {
        parent::setUp();
        $this->calculator = new InvoiceTotalsCalculator();
        
        // Create test products
        Product::factory()->create(['id' => 100, 'price' => 10.00, 'tax_rate' => 0]);
        Product::factory()->create(['id' => 101, 'price' => 20.00, 'tax_rate' => config('taxes.iva')]);
        Product::factory()->create(['id' => 102, 'price' => 15.00, 'tax_rate' => config('taxes.iva')]);
    }

    public function test_single_item_without_tax()
    {
        $items = [
            ['product_id' => 100, 'quantity' => 2, 'unit_price' => 10.00, 'tax_rate' => 0],
        ];

        $result = $this->calculator->calculate($items);

        $this->assertEquals(20.00, $result['subtotal']);
        $this->assertEquals(0.00, $result['tax']);
        $this->assertEquals(20.00, $result['total']);
    }

    public function test_single_item_with_iva()
    {
        $items = [
            ['product_id' => 101, 'quantity' => 1, 'unit_price' => 20.00, 'tax_rate' => config('taxes.iva')],
        ];

        $result = $this->calculator->calculate($items);

        $this->assertEquals(20.00, $result['subtotal']);
        $this->assertEquals(3.00, $result['tax']); // 20 * 15% = 3
        $this->assertEquals(23.00, $result['total']);
    }

    public function test_multiple_items_mixed_taxes()
    {
        $items = [
            ['product_id' => 100, 'quantity' => 3, 'unit_price' => 10.00, 'tax_rate' => 0],
            ['product_id' => 101, 'quantity' => 2, 'unit_price' => 20.00, 'tax_rate' => config('taxes.iva')],
        ];

        $result = $this->calculator->calculate($items);

        // Subtotal: 3*10 + 2*20 = 30 + 40 = 70
        // Tax: 0 + (40*15%) = 0 + 6 = 6
        // Total: 76
        $this->assertEquals(70.00, $result['subtotal']);
        $this->assertEquals(6.00, $result['tax']);
        $this->assertEquals(76.00, $result['total']);
    }

    public function test_complex_multiple_items_different_taxes()
    {
        $items = [
            ['product_id' => 100, 'quantity' => 5, 'unit_price' => 10.00, 'tax_rate' => 0],
            ['product_id' => 101, 'quantity' => 2, 'unit_price' => 20.00, 'tax_rate' => config('taxes.iva')],
            ['product_id' => 102, 'quantity' => 1, 'unit_price' => 15.00, 'tax_rate' => config('taxes.iva')],
        ];

        $result = $this->calculator->calculate($items);

        // Subtotal: 5*10 + 2*20 + 1*15 = 50 + 40 + 15 = 105
        // Tax: 0 + (40*15%) + (15*15%) = 0 + 6 + 2.25 = 8.25
        // Total: 113.25
        $this->assertEquals(105.00, $result['subtotal']);
        $this->assertEquals(8.25, $result['tax']);
        $this->assertEquals(113.25, $result['total']);
    }

    public function test_rounding_to_two_decimals()
    {
        $items = [
            ['product_id' => 101, 'quantity' => 3, 'unit_price' => 19.99, 'tax_rate' => config('taxes.iva')],
        ];

        $result = $this->calculator->calculate($items);

        // Subtotal: 3 * 19.99 = 59.97
        // Tax: 59.97 * 15% = 8.9955 ≈ 8.9955 (kept to 4 decimals internally, rounded to 2 for total)
        // Total: 59.97 + 8.9955 = 68.9655 ≈ 68.97
        $this->assertEquals(59.97, $result['subtotal']);
        $this->assertEquals(8.9955, $result['tax'], '', 0.0001); // Allow 4 decimal places
        $this->assertEquals(68.9655, $result['total'], '', 0.0001);
    }

    public function test_with_missing_tax_rate_uses_product_default()
    {
        $items = [
            ['product_id' => 101, 'quantity' => 1, 'unit_price' => 20.00, 'tax_rate' => null],
        ];

        $result = $this->calculator->calculate($items);

        // Should use product default tax rate (15%)
        $this->assertEquals(20.00, $result['subtotal']);
        $this->assertEquals(3.00, $result['tax']);
        $this->assertEquals(23.00, $result['total']);
    }

    public function test_empty_items_returns_zeros()
    {
        $items = [];

        $result = $this->calculator->calculate($items);

        $this->assertEquals(0.0, $result['subtotal']);
        $this->assertEquals(0.0, $result['tax']);
        $this->assertEquals(0.0, $result['total']);
    }
}
