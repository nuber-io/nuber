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
namespace App\Service\Lxd;

use Exception;
use App\Lib\Bytes;
use App\Lxd\LxdClient;
use Origin\Service\Result;
use App\Service\ApplicationService;

/**
 * Getting the disk usage between storage drivers provides in consistent results
 *
 * @method Result dispatch(string $instance)
 */
class LxdDiskUsage extends ApplicationService
{
    use LxdTrait;

    private LxdClient $client;

    protected function initialize(LxdClient $client): void
    {
        $this->client = $client;
    }

    /**
     * @param string $instance
     * @return Result
     */
    protected function execute(string $instance): Result
    {
        try {
            $info = $this->client->instance->info($instance);
        } catch (Exception $exception) {
            return new Result($this->transformException($exception));
        }

        $totalBytes = isset($info['devices']['root']['size']) ? Bytes::fromString($info['devices']['root']['size']) : null;
       
        try {
            $output = $this->client->instance->execCommand($instance, 'du -shm /'); # important -h not bytes

            $used = $this->parseOutput($output);

            return new Result([
                'data' => [
                    'used' => $used,
                    'total' => $totalBytes,
                    'percent' => sprintf('%f', $this->calculatePercentage($used, $totalBytes))
                ]
            ]);
        } catch (Exception $exception) {
            return new Result($this->transformException($exception));
        }
    }

    /**
     * @param integer $used
     * @param integer|null $totalBytes
     * @return float
     */
    private function calculatePercentage(int $used, ?int $totalBytes): float
    {
        if ($totalBytes === null) {
            return 0;
        }

        return ($used / $totalBytes) * 100;
    }

    /**
     * @param string $output
     * @return int
     */
    private function parseOutput(string $output): int
    {
        $lines = explode("\n", $output);

        $firstLine = array_shift($lines);
        $firstLine = preg_replace('/\s+/', ' ', $firstLine);
       
        if (preg_match('/^([\d]+) \/$/', $firstLine, $matches)) {
            return (int) Bytes::fromString($matches[1] . 'MB');
        }
     
        throw new Exception('Error parsing output');
    }
}
