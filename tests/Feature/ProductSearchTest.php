<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_search_returns_results()
    {
        $user = User::factory()->create();
        Product::factory()->create(['name' => 'Agua Mineral', 'code' => 'AM-01', 'price' => 1]);
        Product::factory()->create(['name' => 'Agua Con Gas', 'code' => 'AG-02', 'price' => 2]);

        $this->actingAs($user)
            ->getJson(route('products.search', ['q' => 'Agua']))
            ->assertOk()
            ->assertJsonStructure(['results' => [['id', 'text', 'price', 'tax', 'code']]])
            ->assertJsonCount(2, 'results');
    }
}
