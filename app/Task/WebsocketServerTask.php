<?php
/**
 * Nuber.io
 * Copyright 2020 - 2021 Jamiel Sharief.
 *
 * SPDX-License-Identifier: AGPL-3.0
 *
 * @copyright   Copyright (c) Jamiel Sharief
 * @link        https://www.nuber.io
 * @license     https://opensource.org/licenses/AGPL-3.0 AGPL-3.0 License
 */
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
