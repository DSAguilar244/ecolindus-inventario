<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_name_is_required_when_creating_category()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('categories.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_name_must_be_unique_on_create()
    {
        $user = User::factory()->create();
        Category::factory()->create(['name' => 'UniqueCat']);

        $response = $this->actingAs($user)->post(route('categories.store'), [
            'name' => 'UniqueCat',
        ]);

        $response->assertSessionHasErrors('name');
    }
}
