<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesByCustomerReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_by_customer_view_and_pdf_export()
    {
        $user = User::factory()->create();

        $customerA = Customer::factory()->create(['first_name' => 'Alice']);
        $customerB = Customer::factory()->create(['first_name' => 'Bob']);

        Invoice::factory()->create(['customer_id' => $customerA->id, 'date' => now()->subDays(3), 'total' => 150]);
        Invoice::factory()->create(['customer_id' => $customerB->id, 'date' => now()->subDays(1), 'total' => 250]);

        // View
        $this->actingAs($user)
            ->get(route('reports.sales_by_customer'))
            ->assertOk()
            ->assertSee('Ventas por Cliente');

        // PDF export
        $response = $this->actingAs($user)
            ->get(route('reports.sales_by_customer', ['export' => 'pdf']));

        $response->assertOk();
        $this->assertTrue(str_contains((string) $response->headers->get('content-type'), 'application/pdf') || str_contains($response->getContent(), '%PDF'));
    }
}
