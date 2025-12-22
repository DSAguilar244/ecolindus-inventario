<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigrateProductCategoryCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_migrate_textual_product_categories_creates_categories_and_assigns_ids()
    {
        $user = User::factory()->create();

        // Existing category
        $existing = Category::create(['name' => 'agua']);

        // Ensure textual 'category' column exists for the purpose of this test (migration that drops it may have run)
        \Illuminate\Support\Facades\Schema::table('products', function (\Illuminate\Database\Schema\Blueprint $table) {
            if (! Schema::hasColumn('products', 'category')) {
                $table->string('category')->default('')->after('name');
            }
        });

        // Products: one 'agua' (no category_id), one 'botella' (no category_id), and one already with category_id
        $p1 = Product::factory()->create(['name' => 'P1', 'category' => 'agua', 'category_id' => null]);
        $p2 = Product::factory()->create(['name' => 'P2', 'category' => 'Botella', 'category_id' => null]);
        $p3 = Product::factory()->create(['name' => 'P3', 'category' => 'agua', 'category_id' => $existing->id]);

        // Run command with --force
        $this->actingAs($user)->artisan('products:migrate-category', ['--force' => true])->assertExitCode(0);

        // p1 should get existing category id (case-insensitive match)
        $this->assertDatabaseHas('products', ['id' => $p1->id, 'category_id' => $existing->id]);

        // p2 should get new category created
        $newCat = Category::whereRaw('LOWER(name) = ?', ['botella'])->first();
        $this->assertNotNull($newCat);
        $this->assertDatabaseHas('products', ['id' => $p2->id, 'category_id' => $newCat->id]);
    }
}
