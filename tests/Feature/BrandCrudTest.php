<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_shows_brands()
    {
        $user = User::factory()->create();
        Brand::create(['name' => 'Acme']);
        Brand::create(['name' => 'Beta']);

        $response = $this->actingAs($user)->get(route('brands.index'));
        $response->assertStatus(200);
        $response->assertSee('Acme');
        $response->assertSee('Beta');
    }

    public function test_can_create_brand()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('brands.store'), [
            'name' => 'NuevaMarca',
            'description' => 'Descripción de prueba',
        ]);

        $response->assertRedirect(route('brands.index'));
        $this->assertDatabaseHas('brands', ['name' => 'NuevaMarca']);
    }

    public function test_can_update_brand()
    {
        $user = User::factory()->create();
        $brand = Brand::create(['name' => 'Original']);

        $response = $this->actingAs($user)->put(route('brands.update', $brand), [
            'name' => 'Modificada',
            'description' => 'Editada',
        ]);

        $response->assertRedirect(route('brands.index'));
        $this->assertDatabaseHas('brands', ['id' => $brand->id, 'name' => 'Modificada']);
    }

    public function test_can_delete_brand()
    {
        $user = User::factory()->create();
        $brand = Brand::create(['name' => 'ToDelete']);

        $response = $this->actingAs($user)->delete(route('brands.destroy', $brand));

        $response->assertRedirect(route('brands.index'));
        $this->assertDatabaseMissing('brands', ['id' => $brand->id]);
    }
}
