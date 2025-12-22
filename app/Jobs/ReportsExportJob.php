<?php

namespace App\Jobs;

use App\Models\ExportRecord;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ReportsExportJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $year;
    public int $month;
    public int|null $userId;
    public string $token;

    public function __construct(int $year, int $month, ?int $userId, string $token)
    {
        $this->year = $year;
        $this->month = $month;
        $this->userId = $userId;
        $this->token = $token;
    }

    public function handle(): void
    {
        // Generate CSV export for the month
        $filename = "exports/report_{$this->year}_{$this->month}_{$this->token}.csv";
        $fullPath = storage_path('app/' . $filename);
        try {
            // Ensure directory
            $dir = dirname($fullPath);
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $fp = fopen($fullPath, 'w');
            // header
            fputcsv($fp, ['invoice_id', 'invoice_number', 'date', 'customer', 'total', 'payment_cash', 'payment_transfer']);

            // Simple export: find invoices for month
            $start = \Carbon\Carbon::create($this->year, $this->month, 1)->startOfMonth();
            $end = (clone $start)->endOfMonth();

            $query = \App\Models\Invoice::whereBetween('date', [$start, $end])->with(['customer', 'payment']);
            if ($this->userId) {
                $query->where('user_id', $this->userId);
            }

            $query->chunk(200, function ($invoices) use ($fp) {
                foreach ($invoices as $inv) {
                    $row = [
                        $inv->id,
                        $inv->invoice_number,
                        $inv->date->format('Y-m-d'),
                        $inv->customer?->first_name . ' ' . $inv->customer?->last_name,
                        $inv->total,
                        $inv->payment?->cash_amount ?? 0,
                        $inv->payment?->transfer_amount ?? 0,
                    ];
                    fputcsv($fp, $row);
                }
            });

            fclose($fp);

            // Update export record
            $export = ExportRecord::where('token', $this->token)->first();
            if ($export) {
                $export->update(['path' => $filename, 'status' => 'done', 'finished_at' => now()]);
            }

            Log::info('Export generated', ['token' => $this->token, 'path' => $filename]);
        } catch (\Throwable $e) {
            Log::error('Export job failed: '.$e->getMessage());
            $export = ExportRecord::where('token', $this->token)->first();
            if ($export) {
                $export->update(['status' => 'failed', 'meta' => ['error' => $e->getMessage()]]);
            }
            throw $e;
        }
    }
}
