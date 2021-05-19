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

use App\Lxd\LxdClient;
use Origin\Service\Result;
use App\Service\ApplicationService;

/**
 * Before migration lets do some checks
 *
 * @method Result dispatch(string $host)
 */
class LxdDetectNetworkInterfaces extends ApplicationService
{
    use LxdTrait;

    private LxdClient $client;

    protected function initialize(LxdClient $client): void
    {
        $this->client = $client;
    }

    /**
     * @param string $instance
     * @return \Origin\Service\Result
     */
    protected function execute(string $instance): Result
    {
        $hashMap = $this->buildHashMap();

        $result = [
            'eth0' => null,
            'eth1' => null
        ];

        // TODO: info would of have been called in the controller as well, create a LxdGetInstanceInfo
        $info = $this->client->instance->info($instance);

        foreach (['eth0','eth1'] as $interface) {
            if (isset($info['expanded_devices'][$interface])) {
                $device = $info['expanded_devices'][$interface];

                $hash = md5(json_encode([
                    'nictype' => $device['nictype'] ?? null,
                    'parent' => $device['parent'] ?? null,
                    'type' => $device['type'] ?? null
                ]));

                $result[$interface] = $hashMap[$hash] ?? null;
            }
        }

        return new Result(['data' => $result]);
    }

    /**
     * Creates a map of hashes based to help with detection
     *
     * @return array
     */
    private function buildHashMap() : array
    {
        $hashMap = [];
      
        foreach ($this->client->profile->list() as $profile) {
            if (in_array($profile['name'], ['nuber-nat','nuber-bridged','nuber-macvlan'])) {
                $payload = [
                    'nictype' => $profile['devices']['eth0']['nictype'] ?? null,
                    'parent' => $profile['devices']['eth0']['parent'] ?? null,
                    'type' => $profile['devices']['eth0']['type'] ?? null
                ];
         
                $hash = md5(json_encode($payload));
                $hashMap[$hash] = $profile['name'];
            }
        }
  
        return $hashMap;
    }
}
