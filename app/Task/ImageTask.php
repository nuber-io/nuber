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
