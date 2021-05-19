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
namespace App\Service;

use Origin\Service\Result;

/**
 * @deprecated remove this
 * @method Result dispatch()
 */
class LinuxInfo extends ApplicationService
{
    protected function execute(): Result
    {
        $cpu = $this->parse(shell_exec('lscpu')); // /proc/cpuinfo - not helpful
        $mem = $this->parse(file_get_contents('/proc/meminfo'));

        $total = $this->parseMemory($mem['MemTotal']);
        $used = $total - $this->parseMemory($mem['MemFree']);

        return new Result([
            'data' => [
                'summary' => [
                    'architecture' => $cpu['Architecture'], // e.g. x86_64
                    'cpus' => (int) $cpu['CPU(s)'],
                    'memory' => $total,
                    'used' => $used
                ],
                'cpu' => $cpu,
                'mem' => $mem
            ]
        ]);
    }

    /**
     * Parses from a string a KB number and converts to bytes
     *
     * @param string $data
     * @return integer
     */
    private function parseMemory(string $data): int
    {
        // 2035604 kB
        preg_match('/([0-9]+) kB/', $data, $matches);

        $kb = (int) $matches[1];

        return $kb * 1024;
    }

    private function parse(string $data): array
    {
        $out = [];
        foreach (explode("\n", $data) as $line) {
            if (preg_match('/:/', $line)) {
                list($key, $value) = explode(':', $line);
                $out[trim($key)] = trim($value);
            }
        }

        return $out;
    }
}
