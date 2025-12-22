<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_update_and_delete_customer()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('customers.store'), [
                'identification' => '1234567890',
                'first_name' => 'Test',
                'address' => 'Calle Falsa 123',
                'last_name' => 'User',
            ])
            ->assertRedirect(route('customers.index'));

        $this->assertDatabaseHas('customers', ['identification' => '1234567890']);
        $this->assertDatabaseHas('customers', ['identification' => '1234567890', 'address' => 'Calle Falsa 123']);

        $customer = Customer::firstWhere('identification', '1234567890');

        $this->actingAs($user)
            ->put(route('customers.update', $customer), ['identification' => '1234567890', 'first_name' => 'Updated', 'address' => 'New address 456'])
            ->assertRedirect(route('customers.index'));

        $this->assertDatabaseHas('customers', ['first_name' => 'Updated']);
        $this->assertDatabaseHas('customers', ['first_name' => 'Updated', 'address' => 'New address 456']);

        $this->actingAs($user)
            ->delete(route('customers.destroy', $customer))
            ->assertRedirect(route('customers.index'));

        $this->assertDatabaseMissing('customers', ['identification' => '1234567890']);
    }

    public function test_ajax_update_customer_returns_json_and_updates()
    {
        $user = User::factory()->create();

        $customer = Customer::factory()->create(['identification' => '5555', 'first_name' => 'Before']);

        $this->actingAs($user)
            ->putJson(route('customers.update', $customer), ['identification' => '5555', 'first_name' => 'After'])
            ->assertStatus(200)
            ->assertJson(['message' => 'Cliente actualizado']);

        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'first_name' => 'After']);
    }
}
