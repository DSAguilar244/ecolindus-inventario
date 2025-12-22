<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;

class Kernel extends ConsoleKernel
{
    /**
     * Register the commands for the application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\CreateAdminUser::class,
    ];
    protected function schedule($schedule){}

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
