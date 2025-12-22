<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class InvoiceConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_concurrent_invoice_creation_generates_sequential_numbers()
    {
        // Skip test for SQLite as SELECT FOR UPDATE is not supported in the same way
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            $this->markTestSkipped('This concurrency test is skipped on SQLite.');
        }

        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 100, 'price' => 100]);

        // Ensure invoice_numbers seed row exists
        DB::table('invoice_numbers')->insert(['prefix' => 'INV', 'last_number' => 0, 'created_at' => now(), 'updated_at' => now()]);

        $processes = [];
        $count = 5;

        for ($i = 1; $i <= $count; $i++) {
            $ident = 'conc-'.(1000 + $i);
            $cmd = [
                PHP_BINARY,
                __DIR__.'/../ConsoleScripts/create_invoice.php',
                $ident,
                (string) $product->id,
            ];

            $p = new Process($cmd);
            $p->start();
            $processes[] = $p;
        }

        // Wait for all to finish
        foreach ($processes as $p) {
            $p->wait();
            $this->assertSame(0, $p->getExitCode(), 'Process failed: '.$p->getErrorOutput());
        }

        // Validate that we have $count invoices created and numbers sequential
        $invoices = DB::table('invoices')->orderBy('id')->get();
        $this->assertCount($count, $invoices);

        $expected = [];
        for ($i = 1; $i <= $count; $i++) {
            $expected[] = sprintf('INV-%s', str_pad($i, 6, '0', STR_PAD_LEFT));
        }

        $actual = array_map(fn ($r) => $r->invoice_number, (array) $invoices->toArray());
        $this->assertEquals($expected, $actual);

        // Check invoice_numbers.last_number was updated appropriately
        $row = DB::table('invoice_numbers')->first();
        $this->assertEquals($count, $row->last_number);
    }
}
