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
