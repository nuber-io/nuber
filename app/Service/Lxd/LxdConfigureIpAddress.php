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
use App\Lxd\LxdClient;
use Origin\Service\Result;
use App\Service\ApplicationService;

/**
 * Configures an instance IP address on a virtual network, and proxies are reconfigured too.
 *
 * @method Result dispatch(string $instance, string $ip4 = null, string $ip6 = null))
 */
class LxdConfigureIpAddress extends ApplicationService
{
    use LxdTrait;
    private LxdClient $client;
    
    protected function initialize(LxdClient $client): void
    {
        $this->client = $client;
    }

    /**
     * @param string $instance
     * @param string $ip4
     * @param string $ip6
     * @return Result
     */
    protected function execute(string $instance, string $ip4 = null, string $ip6 = null): Result
    {
        try {
            $info = $this->client->instance->info($instance);

            // TODO: this code is being repeated everywhere, migrate into own class
            $device = $info['expanded_devices']['eth0'] ?? null;
            $nicType = $info['expanded_devices']['eth0']['nictype'] ?? null;
            $parent = $info['expanded_devices']['eth0']['parent'] ?? null;

            if (! $device) {
                return new Result([
                    'error' => [
                        'message' => 'eth0 does not exist',
                        'error' => 500
                    ]
                ]);
            }

            if ($nicType === 'bridged' && $parent !== 'nuber-bridged') {
                $device['ipv4.address'] = $ip4;
                $device['ipv6.address'] = $ip6;

                $info['devices']['eth0'] = $device;
            }

            $this->client->instance->update($instance, $info);

            if ($device) {
                $result = (new LxdConvertProxies($this->client))->dispatch($instance);
                if ($result->error()) {
                    return $result;
                }
            }

            return new Result([
                'data' => [
                    'device' => $device,
                    'info' => $info
                ]
            ]);
        } catch (Exception $exception) {
            return new Result(
                $this->transformException($exception)
            );
        }
    }
}
