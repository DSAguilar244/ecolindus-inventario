<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierDuplicateTest extends TestCase
{
    use RefreshDatabase;

    // Test disabled - suppliers routes have been removed
    // public function test_duplicate_supplier_creation_returns_409_for_ajax()
    // {
    //     $user = User::factory()->create();
    //     Supplier::create(['name' => 'Acme Supplies', 'email' => 'acme@example.com']);
    //
    //     $response = $this->actingAs($user)->postJson(route('suppliers.store'), [
    //         'name' => 'Acme Supplies',
    //         'email' => 'acme@example.com',
    //     ]);
    //
    //     $response->assertStatus(409);
    //     $response->assertJsonStructure(['message', 'supplier']);
    // }
}

