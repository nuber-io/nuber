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
 * Setting or removing IP addresses require proxies to be changed.
 *
 * @internal ipv6 addresses are square notation e.g connect=tcp:[2001:db8::1]:80
 * @see https://lxd.readthedocs.io/en/latest/instances/#type-proxy
 *
 * Also for IPV6 you have to use [::] not 0.0.0.0
 *
 * @method Result dispatch(string $instance))
 */
class LxdConvertProxies extends ApplicationService
{
    use LxdTrait;

    private LxdClient $client;
    
    protected function initialize(LxdClient $client): void
    {
        $this->client = $client;
    }

    /**
     * Only supports IPv4 for now, some code added for IPv6 but not usable
     *
     * @param string $instance
     * @return Result
     */
    protected function execute(string $instance): Result
    {
        try {
            $info = $this->client->instance->info($instance);

            // Find the nuberbr0 lxdbr0, if it does not exists then user is not using virtual network
            $device = $this->findNetworkBridgeDevice($info);
        
            if ($device) {
                $ipAddress = $info['devices'][$device]['ipv4.address'] ?? null;
                if ($ipAddress) {
                    $info['devices'] = $this->convertToNAT($info['devices'], $this->client->hostName(), $ipAddress);
                } else {
                    $info['devices'] = $this->convertFromNAT($info['devices'], $this->client->hostName(), null);
                }
            }
          
            $this->client->instance->update($instance, $info);
    
            return new Result([
                'data' => $info['devices']
            ]);
        } catch (Exception $exception) {
            return new Result(
                $this->transformException($exception)
            );
        }
    }

    private function convertToNAT(array $devices, string $host, string $ipAddress): array
    {
        // Be ready for ipv6
        $zero = strpos($ipAddress, ':') !== false ? '[::]' : '0.0.0.0';

        foreach ($devices as $key => $device) {
            if ($device['type'] === 'proxy') {
                list($listenProtocol, $ignore, $listenPort) = explode(':', $device['listen']);
                $device['listen'] = "{$listenProtocol}:{$host}:{$listenPort}";

                list($connectProtocol, $ignore, $connectPort) = explode(':', $device['connect']);
                $device['connect'] = "{$connectProtocol}:{$zero}:{$connectPort}";
                $device['nat'] = 'true';
                $devices[$key] = $device;
            }
        }

        return $devices;
    }

    private function convertFromNAT(array $devices, string $host, string $ipAddress = null): array
    {
        // Be ready for ipv6
        $zero = $ipAddress && strpos($ipAddress, ':') !== false ? '[::]' : '0.0.0.0';

        foreach ($devices as $key => $device) {
            if ($device['type'] === 'proxy' && isset($device['nat']) && $device['nat'] === 'true') {
                list($listenProtocol, $ignore, $listenPort) = explode(':', $device['listen']);
                $device['listen'] = "{$listenProtocol}:{$zero}:{$listenPort}";

                list($connectProtocol, $ignore, $connectPort) = explode(':', $device['connect']);
                $device['connect'] = "{$connectProtocol}:127.0.0.1:{$connectPort}";
                unset($device['nat']);
                $devices[$key] = $device;
            }
        }
  
        return $devices;
    }
}
