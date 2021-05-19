<?php
declare(strict_types = 1);
namespace App\Task;

use Origin\Schedule\Task;
use Origin\Schedule\Schedule;

class WebsocketServerTask extends Task
{
    protected $name = 'Websocket Server';
    protected $description = '';

    protected function handle(Schedule $schedule): void
    {
        $schedule->command('cd /var/www/websocket && node server.js')
            ->everyMinute()
            ->background()
            ->limit(1);
    }
}
