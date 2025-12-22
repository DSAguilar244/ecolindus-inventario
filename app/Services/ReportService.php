<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ReportService
{
    public function queueMonthlyExport(int $year, int $month, int $userId = null): string
    {
        $token = 'export_'.md5("{$year}_{$month}_{$userId}_".now());
        // create export record
        try {
            \App\Models\ExportRecord::create(['token' => $token, 'user_id' => $userId, 'type' => 'monthly', 'status' => 'queued']);
            // dispatch job
            dispatch(new \App\Jobs\ReportsExportJob($year, $month, $userId, $token));
            Log::info('Queued monthly export', ['token' => $token, 'year' => $year, 'month' => $month, 'user' => $userId]);
        } catch (\Throwable $e) {
            Log::warning('Could not queue monthly export: '.$e->getMessage());
        }

        return $token;
    }

    public function cacheKeyForMonth(int $year, int $month): string
    {
        return "report:monthly:{$year}-{$month}";
    }

    public function invalidateMonthlyCache(int $year, int $month): void
    {
        try {
            Cache::forget($this->cacheKeyForMonth($year, $month));
        } catch (\Exception $e) {
            Log::warning('Could not invalidate monthly cache: '.$e->getMessage());
        }
    }
}
