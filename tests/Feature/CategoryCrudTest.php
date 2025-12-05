<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_shows_categories()
    {
        $user = User::factory()->create();
        Category::create(['name' => 'Cat A']);
        Category::create(['name' => 'Cat B']);

        $response = $this->actingAs($user)->get(route('categories.index'));
        $response->assertStatus(200);
        $response->assertSee('Cat A');
        $response->assertSee('Cat B');
    }

    public function test_can_create_category()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('categories.store'), [
            'name' => 'NuevaCat',
            'description' => 'Descripción',
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', ['name' => 'NuevaCat']);
    }

    public function test_can_update_category()
    {
        $user = User::factory()->create();
        $cat = Category::create(['name' => 'OriginalCat']);

        $response = $this->actingAs($user)->put(route('categories.update', $cat), [
            'name' => 'CatMod',
            'description' => 'Editada',
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', ['id' => $cat->id, 'name' => 'CatMod']);
    }

    public function test_can_delete_category()
    {
        $user = User::factory()->create();
        $cat = Category::create(['name' => 'ToDeleteCat']);

        $response = $this->actingAs($user)->delete(route('categories.destroy', $cat));

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseMissing('categories', ['id' => $cat->id]);
    }
}
