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
        // Regenerate the XML sitemap (hreflang clusters + blog/product URLs) daily,
        // then push the fresh URLs to IndexNow for near-instant discovery.
        // Requires the system cron to run `php artisan schedule:run` every minute.
        $schedule->command('sitemap:generate')->dailyAt('03:30');
        $schedule->command('indexnow:submit')->dailyAt('03:35');
        $schedule->command('marketing:dispatch')->everyFifteenMinutes()->withoutOverlapping();

        if (config('queue.default') !== 'sync') {
            $schedule->command('queue:work', [
                '--stop-when-empty',
                '--max-time' => 50,
                '--max-jobs' => 20,
                '--sleep' => 1,
                '--tries' => 3,
                '--timeout' => 40,
                '--no-interaction',
            ])->everyMinute()->withoutOverlapping(5);
        }
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
