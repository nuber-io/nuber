<?php
declare(strict_types = 1);
namespace App\Task;

use Origin\Schedule\Task;
use Origin\Schedule\Schedule;

class BackupTask extends Task
{
    protected $name = 'Backup';
    protected $description = 'Schedules and executes the backups';

    protected function handle(Schedule $schedule): void
    {
        $schedule->command('bin/console schedule:backups hourly')
            ->hourly()
            ->background();

        $schedule->command('bin/console schedule:backups daily')
            ->daily()
            ->background();

        $schedule->command('bin/console schedule:backups weekly')
            ->weekly()
            ->background();

        $schedule->command('bin/console schedule:backups monthly')
            ->monthly()
            ->background();
    }
}
