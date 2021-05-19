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
namespace App\Model;

use Origin\Model\Model;
use Origin\Model\Concern\Delocalizable;
use Origin\Model\Concern\Timestampable;

class ApplicationModel extends Model
{
    use Delocalizable;
    use Timestampable;

    protected function initialize(array $config): void
    {
    }
}
