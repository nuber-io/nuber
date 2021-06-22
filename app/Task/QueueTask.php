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
