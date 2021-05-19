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
namespace App\Lxd;

class Lxd
{
    private static ?string $host = null; //# important
    
    private static array $hosts = [];
   
    /**
     * Sets or gets host to be used by the lxd endpoints
     *
     * @param string $host
     * @return string|null
     */
    public static function host(string $host = null): ?string
    {
        if (is_null($host)) {
            return static::$host;
        }

        return static::$host = $host;
    }

    /**
     * Gets a configured client
     */
    public static function client() : LxdClient
    {
        return new LxdClient(static::$host);
    }

    /**
     * Sets or gets the hosts
     *
     * @param array $hosts
     * @return array
     */
    public static function hosts(array $hosts = []): array
    {
        if (empty($hosts)) {
            return static::$hosts;
        }

        return static::$hosts = $hosts;
    }
}
