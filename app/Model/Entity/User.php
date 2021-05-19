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
namespace App\Model\Entity;

use Origin\Model\Entity;

class User extends Entity
{
    protected $virtual = ['name'];

    protected function getName()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
