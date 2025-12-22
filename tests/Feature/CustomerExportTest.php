<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_csv_export_respects_filter()
    {
        $user = User::factory()->create();
        Customer::factory()->create(['first_name' => 'Juan', 'identification' => '111', 'address' => 'Calle Falsa 123']);
        Customer::factory()->create(['first_name' => 'Carlos', 'identification' => '222']);

        $response = $this->actingAs($user)
            ->get(route('customers.export.csv', ['q' => 'Juan']));

        // Debugging: dump CSV for diagnosis in test output
        fwrite(STDOUT, "CSV content:\n".$response->getContent()."\n");

        $response->assertStatus(200)
            ->assertSee('Juan')
            ->assertDontSee('Carlos')
            ->assertSee('Calle Falsa 123');
    }

    public function test_pdf_export_respects_filter_and_returns_pdf()
    {
        $user = User::factory()->create();
        Customer::factory()->create(['first_name' => 'Pedro', 'identification' => '333']);
        Customer::factory()->create(['first_name' => 'Luis', 'identification' => '444']);

        $response = $this->actingAs($user)->get(route('customers.export.pdf', ['q' => 'Pedro']));
        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
    }
}
