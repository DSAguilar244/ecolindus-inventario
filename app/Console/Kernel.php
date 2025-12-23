<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;

class Kernel extends ConsoleKernel
{
    /**
     * Register the commands for the application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\CreateAdminUser::class,
        \App\Console\Commands\RefreshDashboardCaches::class,
        \App\Console\Commands\CheckDbLatency::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // Only run scheduled refreshes in production
        if (env('APP_ENV') === 'production') {
            // Refresh dashboard caches every 1 minute so web requests hit cache instead of DB
            $schedule->command('dashboard:refresh-caches')->everyMinute()->withoutOverlapping();
        }
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
