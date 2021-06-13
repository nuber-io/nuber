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
 * @method Result dispatch()
 */
class LxdResourceUsage extends ApplicationService
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
    protected function execute(): Result
    {
        try {
            $data = $this->calculateUsage();
        } catch (Exception $exception) {
            return new Result($this->transformException($exception));
        }

        return new Result([
            'data' => $data
        ]);
    }

    /**
     * Takes 6 seconds
     *
     * @return array
     */
    private function calculateUsage() : array
    {
        $resources = $this->client->resource->get();

        // Request data with CPU info twice with 1 second between
        $before = $this->client->instance->list();
        sleep(1);
        $now = $this->client->instance->list();

        // Here on purpose to help better calculutions
        $before = collection($before)->indexBy('name')->toArray();
        $now = collection($now)->indexBy('name')->toArray();

        $instances = [];
        foreach ($now as $instance => $info) {
            // ignore non running containers
            if ($info['status'] !== 'Running') {
                $instances[$instance] = [
                    'memory' => 0,
                    'disk' => 0,
                    'cpu' => 0
                ];
                continue;
            }
            $cores = (int) isset($info['config']['limits.cpu']) ? $info['config']['limits.cpu']  : $resources['cpu']['total']; // u

            $cpuBefore = $before[$instance]['state']['cpu']['usage'];
            $cpuNow = $now[$instance]['state']['cpu']['usage'];

            $instances[$instance] = [
                'memory' => $this->memoryUsage($now[$instance]),
                'disk' => $this->diskUsage($now[$instance]),
                'cpu' => (($cpuNow - $cpuBefore) / ($cores * 1000000000)) * 100, //
            ];
        }

        return $instances;
    }

    /**
     * Undocumented function
     *
     * @param array $info
     * @return float
     */
    private function memoryUsage(array $info) : float
    {
        if (empty($info['state']['memory']['usage']) || empty($info['config']['limits.memory'])) {
            return 0;
        }
        $out = 0;

        $bytes = Bytes::fromString($info['config']['limits.memory']);
        if ($bytes) {
            $out = ($info['state']['memory']['usage'] / $bytes) * 100 ;
        }
       
        return round($out, 2);
    }

    /**
     * Originally when i started developing i was using micro containers, so size had to be precise however this is now
     * a bottleneck, so i am going to revert back to using the data lxd gives us.
     *
     * @param array $info
     * @return float
     */
    public function diskUsage(array $info) : float
    {
        if (empty($info['state']['disk']['root']['usage']) || empty($info['devices']['root']['size'])) {
            return 0;
        }
        $out = 0;
      
        $bytes = Bytes::fromString($info['devices']['root']['size']);
        if ($bytes) {
            $out = ($info['state']['disk']['root']['usage'] / $bytes) * 100 ;
        }

        return round($out, 2);
    }
}
