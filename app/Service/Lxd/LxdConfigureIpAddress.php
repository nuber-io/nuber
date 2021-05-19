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
 * Configures an instance IP address on the virtual network, and proxies are reconfigured too.
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
     * Undocumented function
     *
     * @param string $host
     * @param string $instance
     * @param string $profile  e.g. nuber-bridged or nuber-nat
     * @param string $ip
     * @return Result
     */
    protected function execute(string $instance, string $ip4 = null, string $ip6 = null): Result
    {
        try {
            $info = $this->client->instance->info($instance);

            // find the interface for network bridge, should be eth0
            $device = $this->findNetworkBridgeDevice($info);
            if ($device) {
                $profileInfo = $this->client->profile->get('nuber-nat');
                $info['devices'][$device] = $profileInfo['devices'][$device];
                $info['devices'][$device]['ipv4.address'] = $ip4;
                $info['devices'][$device]['ipv6.address'] = $ip6;
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
