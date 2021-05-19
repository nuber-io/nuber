<?php
declare(strict_types = 1);
namespace App\Task;

use Origin\Schedule\Task;
use Origin\Schedule\Schedule;

class QueueTask extends Task
{
    protected $name = 'Queue';
    protected $description = '';

    protected function handle(Schedule $schedule): void
    {
        // Keep open for 5 minutes
        $schedule->command('bin/console queue:worker backups -d --seconds=300')
            ->cron('59 * * * *')
            ->background()
            ->processes(5)
            ->limit(5);
    }
}
