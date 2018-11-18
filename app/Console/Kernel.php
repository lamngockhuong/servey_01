<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\CloseSurveyCommand::class,
        Commands\OpenSurveyCommand::class,
        Commands\ResendReminderEmailCommand::class,
        Commands\BackupDatabaseCommand::class,
        Commands\ResendEmailRemindTimeRemaining::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('command:auto-change-status')->daily();
        $schedule->command('command:resend-reminder-email')->everyMinute();
        $schedule->command('command:open-survey')->everyMinute();
        $schedule->command('backup:database')->weekly();
        $schedule->command('command:resend-email-remind-time-remaining')->everyMinute();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
