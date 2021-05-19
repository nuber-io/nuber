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
namespace App\Lib;

/**
 * Already have this
 */
class Bytes
{
    /**
     * Parses a string e.g. 1024 MB and gets the bytes
     *
     * @param string $data
     * @return int
     */
    public static function fromString(string $data): int
    {
        $units = ['B','KB','MB','GB','TB','PB','EB','ZB','YB'];
      
        preg_match('/(?P<value>[0-9]+)\s?(?P<unit>B|MB|GB|TB|TB|PB|EB|ZB|YB)/i', $data, $matches);
        $bytes = 0;

        if ($matches) {
            $unit = array_search(strtoupper($matches['unit']), $units);
        
            $bytes = (int) $matches['value'] * pow(1024, $unit);
        }

        return $bytes;
    }

    /**
     * Converts bytes into a human readable string e.g. 1 GB
     *
     * @param integer $bytes
     * @return string
     */
    public static function toString(int $bytes): string
    {
        $map = ['B','KB','MB','GB','TB','PB','EB','ZB','YB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $map[$i];
    }
}
