<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesByProductReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_by_product_view_and_pdf_export()
    {
        $user = User::factory()->create();

        $productA = Product::factory()->create(['name' => 'Prod A', 'price' => 10]);
        $productB = Product::factory()->create(['name' => 'Prod B', 'price' => 20]);

        $invoice = Invoice::factory()->create(['date' => now()]);
        InvoiceItem::create(['invoice_id' => $invoice->id, 'product_id' => $productA->id, 'quantity' => 2, 'unit_price' => 10]);
        InvoiceItem::create(['invoice_id' => $invoice->id, 'product_id' => $productB->id, 'quantity' => 1, 'unit_price' => 20]);

        $this->actingAs($user)
            ->get(route('reports.sales_by_product'))
            ->assertOk()
            ->assertSee('Ventas por Producto');

        $response = $this->actingAs($user)
            ->get(route('reports.sales_by_product', ['export' => 'pdf']));

        $response->assertOk();
        $this->assertTrue(str_contains((string) $response->headers->get('content-type'), 'application/pdf') || str_contains($response->getContent(), '%PDF'));
    }
}
