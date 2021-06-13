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
        $result = [
            'eth0' => null,
            'mac0' => null,
            'eth1' => null,
            'mac1' => null
        ];

        // TODO: info would of have been called in the controller as well, create a LxdGetInstanceInfo
        $info = $this->client->instance->info($instance);

        foreach (['eth0','eth1'] as $interface) {
            if (isset($info['expanded_devices'][$interface])) {
                $device = $info['expanded_devices'][$interface];

                // TODO: fixes a warning add to main repo
                $type = $device['nictype'] ?? null;
                if ($type === 'bridged') {
                    $result[$interface] = $device['parent'];
                } elseif ($type === 'macvlan') {
                    $result[$interface] = 'nuber-macvlan';
                }
            }
        }

        $result['mac0'] = $this->detectMacAdddress('eth0', $info);
        $result['mac1'] = $this->detectMacAdddress('eth1', $info);
        
        return new Result(['data' => $result]);
    }

    /**
     * @param string $interface
     * @param array $info
     * @return string|null
     */
    private function detectMacAdddress(string $interface, array $info) : ?string
    {
        $default = $info['config']["volatile.{$interface}.hwaddr"] ?? null;
        $static = $info['expanded_devices'][$interface]['hwaddr'] ?? null;

        return $default ?: $static;
    }
}
