<?php

namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateProductCategoryToCategoryId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * --dry-run: do not perform writes
     * --force: run without confirmation
     *
     * @var string
     */
    protected $signature = 'products:migrate-category {--dry-run} {--force} {--chunk=500}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate textual product category values to categories table and update products.category_id';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $chunk = (int) $this->option('chunk');

        $productsWithCategory = DB::table('products')
            ->whereNotNull('category')
            ->where('category', '<>', '')
            ->whereNull('category_id')
            ->select('category')
            ->distinct()
            ->pluck('category');

        if ($productsWithCategory->isEmpty()) {
            $this->info('No products with textual category found. Nothing to do.');

            return 0;
        }

        $this->info('Found '.$productsWithCategory->count().' unique textual category name(s) to process.');

        if (! $force && ! $dryRun) {
            if (! $this->confirm('This command will create categories and update products in the database. Continue?')) {
                $this->info('Operation aborted. Use --force to skip confirmation, or --dry-run to preview.');

                return 0;
            }
        }

        $totalCreated = 0;
        $totalUpdated = 0;
        $this->output->progressStart($productsWithCategory->count());

        foreach ($productsWithCategory as $categoryName) {
            $name = trim((string) $categoryName);
            if ($name === '') {
                $this->output->progressAdvance();

                continue;
            }

            // Count how many products would be updated
            $count = DB::table('products')->where('category', $name)->whereNull('category_id')->count();
            if ($count === 0) {
                $this->output->progressAdvance();

                continue;
            }

            // Find or create category (case-insensitive)
            if (! $dryRun) {
                $category = Category::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();
                if (! $category) {
                    $category = Category::create(['name' => $name]);
                    $totalCreated++;
                }
                // Update products in chunks to avoid huge transactions
                $updated = 0;
                DB::transaction(function () use (&$updated, $name, $category, $chunk) {
                    DB::table('products')
                        ->where('category', $name)
                        ->whereNull('category_id')
                        ->chunkById($chunk, function ($rows) use (&$updated, $category) {
                            $ids = collect($rows)->pluck('id')->toArray();
                            $updated += DB::table('products')->whereIn('id', $ids)->update(['category_id' => $category->id]);
                        });
                });
                $totalUpdated += $updated;
                $this->info("Updated {$updated} products to category_id={$category->id} ({$name})");
            } else {
                $this->info("[DRY-RUN] Would create/locate category '{$name}' and update {$count} products");
                $totalUpdated += $count; // for dry run summary
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        $this->line('Summary:');
        $this->line('  Categories created: '.$totalCreated);
        $this->line('  Products assigned: '.$totalUpdated);

        if ($dryRun) {
            $this->info('Dry-run mode: no changes were made.');
        }

        return 0;
    }
}
