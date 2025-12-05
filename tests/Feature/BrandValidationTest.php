<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_name_is_required_when_creating_brand()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('brands.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_name_must_be_unique_on_create()
    {
        $user = User::factory()->create();
        Brand::factory()->create(['name' => 'UniqueName']);

        $response = $this->actingAs($user)->post(route('brands.store'), [
            'name' => 'UniqueName',
        ]);

        $response->assertSessionHasErrors('name');
    }
}
