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
namespace App\Lxd\Endpoint;

use App\Lxd\Endpoint;

/**
 * Not an LXC command
 */
class Console extends Endpoint
{
    /**
     * Gets the information about the host
     *
     * @return array
     */
    public function get(string $instance): array
    {
        return $this->sendGetRequest("/instances/{$instance}/console");
    }

    /**
    * Attaches console to the instance
    */
    public function attach(string $instance, array $options = []): array
    {
        $options += ['width' => 80,'height' => 25];

        return $this->sendPostRequest("/instances/{$instance}/console", [
            'data' => $options
        ]);
    }
}
