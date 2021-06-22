<?php
declare(strict_types = 1);
namespace App\Task;

use Origin\Schedule\Task;
use Origin\Schedule\Schedule;

class ImageTask extends Task
{
    protected $name = 'Image';
    protected $description = '';

    protected function startup(): void
    {
    }

    protected function handle(Schedule $schedule): void
    {
        $schedule->command('bin/console bin/console image:list')
            ->daily();
    }

    protected function shutdown(): void
    {
    }
}
