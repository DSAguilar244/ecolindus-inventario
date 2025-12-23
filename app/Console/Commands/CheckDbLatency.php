<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckDbLatency extends Command
{
    protected $signature = 'db:latency {--queries=5}';
    protected $description = 'Measure round-trip latency to the configured DB by running simple SELECTs.';

    public function handle()
    {
        $count = (int) $this->option('queries');
        $this->info("Measuring DB latency with {$count} simple queries...");

        $times = [];
        for ($i = 0; $i < $count; $i++) {
            $start = microtime(true);
            DB::selectOne('SELECT 1');
            $end = microtime(true);
            $times[] = ($end - $start) * 1000; // ms
            $this->line(sprintf('Query %d: %.2f ms', $i + 1, end($times)));
        }

        $avg = array_sum($times) / count($times);
        $min = min($times);
        $max = max($times);

        $this->info(sprintf('Latency (ms) — avg: %.2f, min: %.2f, max: %.2f', $avg, $min, $max));

        return 0;
    }
}
