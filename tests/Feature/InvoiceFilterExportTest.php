<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceFilterExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_filtered_by_date_and_customer()
    {
        $user = User::factory()->create();
        $customerA = Customer::factory()->create(['first_name' => 'Alice']);
        $customerB = Customer::factory()->create(['first_name' => 'Bob']);

        // Create invoices with different dates and customers
        Invoice::factory()->create(['customer_id' => $customerA->id, 'date' => now()->subDays(10), 'total' => 100]);
        Invoice::factory()->create(['customer_id' => $customerB->id, 'date' => now()->subDays(2), 'total' => 200]);

        $response = $this->actingAs($user)
            ->get(route('invoices.export.pdf', ['customer_id' => $customerB->id, 'date_from' => now()->subDays(5)->format('Y-m-d'), 'date_to' => now()->format('Y-m-d')]));

        $response->assertOk();

        // Some servers include charset in the header, so be flexible here.
        $contentType = $response->headers->get('content-type');
        $this->assertTrue(
            str_contains((string) $contentType, 'application/pdf') || str_contains($response->getContent(), '%PDF'),
            'Response should be a PDF (content-type header or PDF signature present)'
        );

        // If the PDF contains the customer's address as plain text, this assertion will succeed.
        // This may depend on PDF binary content, but we try to verify the address appears in the PDF.
        $invoiceWithAddress = Invoice::factory()->create(['customer_id' => $customerB->id, 'date' => now()->subDays(1), 'total' => 300]);
        $customerB->update(['address' => 'Test Address 999']);
        $r = $this->actingAs($user)->get(route('invoices.export.pdf', ['customer_id' => $customerB->id]));
        $r->assertOk();
        $this->assertTrue(str_contains((string) $r->getContent(), 'Test Address 999'), 'PDF should contain the customer address if rendered as text (best-effort)');
    }
}
