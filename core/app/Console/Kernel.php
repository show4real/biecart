<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('package:expire')
            ->daily();

        $schedule->command('account:remove')
            ->daily();

        $schedule->command('package:auto-renew')
            ->everyMinute();

        $schedule->command('queue:work --timeout=60 --tries=1 --once')
            ->everyMinute()
            ->withoutOverlapping()
            ->sendOutputTo(storage_path() . '/logs/queue-jobs.log');
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
