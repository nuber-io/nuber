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
 * This is not an lxd command,
 *
 * [X] list        List logs
 * [ ] read        reads a log
 */
class Log extends Endpoint
{
    /**
     * Gets a list of logs for an instance
     *
     * @param string $name
     * @return array
     */
    public function list(string $name): array
    {
        $path = "/instances/{$name}/logs";

        $response = $this->sendGetRequest($path);
     
        return $this->removeEndpoints(
            $response,
            "/1.0/instances/{$name}/logs/"
        );
    }

    /**
     * Reads a logfile
     *
     * @param string $uuid
     * @return string
     */
    public function get(string $name, string $logfile): string
    {
        return $this->sendGetRequest("/instances/{$name}/logs/{$logfile}");
    }
}
