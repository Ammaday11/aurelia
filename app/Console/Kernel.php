<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // See App\Services\BackupService for what each backup contains and
        // config/backup.php for how many of each type are kept. This only
        // fires if something is actually polling the scheduler — the host
        // needs one cron entry: `* * * * * php artisan schedule:run`
        // (see README's "Backups" section).
        $schedule->command('backup:run daily')->dailyAt('02:00');
        $schedule->command('backup:run weekly')->weeklyOn(1, '02:15'); // Monday
        $schedule->command('backup:run monthly')->monthlyOn(1, '02:30'); // 1st of the month
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
