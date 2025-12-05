<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryDuplicateTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_category_creation_returns_409_for_ajax()
    {
        $user = User::factory()->create();
        Category::create(['name' => 'Beverages']);

        $response = $this->actingAs($user)->postJson(route('categories.store'), [
            'name' => 'Beverages',
            'description' => 'Duplicated',
        ]);

        $response->assertStatus(409);
        $response->assertJsonStructure(['message', 'category']);
    }
}
