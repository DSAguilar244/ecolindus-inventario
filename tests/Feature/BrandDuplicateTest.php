<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandDuplicateTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_brand_creation_fails_and_returns_409_for_ajax()
    {
        $user = User::factory()->create();
        Brand::create(['name' => 'Acme']);

        $response = $this->actingAs($user)->postJson(route('brands.store'), [
            'name' => 'Acme',
            'description' => 'Duplicate',
        ]);

        $response->assertStatus(409);
        $response->assertJsonStructure(['message', 'brand']);
    }
}
