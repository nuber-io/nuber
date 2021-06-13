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
use RuntimeException;
use App\Lxd\LxdClient;
use Origin\Service\Result;
use App\Service\ApplicationService;

/**
 * Changes the network adapter (Bridge,NAT), the static IP address. It will change any proxy devices where a
 * static IP address is set.
 *
 * @internal In order to define a static IPv6 address, the parent managed network needs to have
 * ipv6.dhcp.stateful enabled. However, despite setting this the network stop working so additional
 * configuration is required.
 *
 * @link https://lxd.readthedocs.io/en/latest/instances/#type-proxy
 * @link https://discuss.linuxcontainers.org/t/lxd-ipv6-networking-questions-novice/6961/2 Really great explanation
 *
 * $ lxc network set lxdbr0 ipv6.dhcp.stateful true
 *
 * You could setup a profile like this to use both, but you need to configure the indivual network settings
 * inside the container only eth0 is configured by default when the container is setup. For ubuntu its netplan
 * e.g. /etc/netplan/10-lxc.yaml
 *
 * config: {}
 * description: ""
 *  devices:
 *      eth0:
 *          name: eth0
 *          network: lxdbr0
 *          type: nic
 *      eth1:
 *          name: eth1
 *          nictype: macvlan
 *          parent: wlx28ee52172bcc
 *          type: nic
 * name: multi
 *
 * TODO: Getting bridging working breaks macvlan as it changes the network connection
 *
 * Error: Failed preparing container for start: Failed to start device "eth0": Failed to run: ip link add macfb67e7de link eno1 type macvlan mode bridge: RTNETLINK answers: Device or resource busy
 *
 * Alpine
 * vi /etc/network/interfaces
 * /etc/init.d/networking restart
 *
 * Ubuntu
 * vi /etc/netplan/10-lxc.yaml
 * netplan apply
 *
 * @method Result dispatch(string $instance, string $eth0, string $eth1 = null)
 */
class LxdChangeNetworkSettings extends ApplicationService
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
     * @param string $instance
     * @param string $eth0
     * @param string $eth1
     * @return Result
     */
    protected function execute(string $instance, string $eth0, string $mac0 = null, string $eth1 = null, string $mac1 = null): Result
    {
        try {
            $info = $this->client->instance->info($instance);

            // Remove second interface if it is there
            if (! empty($info['expanded_devices']['eth1'])) {
                unset($info['devices']['eth1']);
                $this->client->device->remove($instance, 'eth1');
            }

            if (isset($info['devices']['eth0'])) {
                // backup virtual network IP address
                $ip4Address = $ip6Address = null;
                if ($info['devices']['eth0']['nictype'] === 'bridged') {
                    $ip4Address = $info['devices']['eth0']['ipv4.address'] ?? null;
                    $ip6Address = $info['devices']['eth0']['ipv6.address'] ?? null;
                }

                // set first interface settings and static address
                $info['devices']['eth0'] = $this->getDeviceConfig('eth0', $eth0);

                unset($info['devices']['eth0']['hwaddr']);
                if ($mac0) {
                    $info['devices']['eth0']['hwaddr'] = $mac0;
                }
              
                if ($info['devices']['eth0']['nictype'] === 'bridged') {
                    $info['devices']['eth0']['ipv4.address'] = $ip4Address;
                    $info['devices']['eth0']['ipv6.address'] = $ip6Address;
                }
            } else {
                // Create the device
                $info['devices']['eth0'] = $this->getDeviceConfig('eth0', $eth0);
            }

            // set second interface
            if ($eth1) {
                $info['devices']['eth1'] = $this->getDeviceConfig('eth1', $eth1);
                unset($info['devices']['eth1']['hwaddr']);
                if ($mac1) {
                    $info['devices']['eth1']['hwaddr'] = $mac1;
                }
            }
        
            $this->client->instance->update($instance, $info);
        } catch (Exception $exception) {
            return new Result(
                $this->transformException($exception)
            );
        }

        return new Result([
            'data' => [
                'info' => $info
            ]
        ]);
    }

    private function getDeviceConfig(string $interface, string $network) : array
    {
        if ($network === 'nuber-macvlan') {
            return [
                'name' => $interface,
                'nictype' => 'macvlan',
                'parent' => $this->getNetworkInterface($this->client->network->list()),
                'type' => 'nic'
            ];
        }

        if ($network === 'nuber-bridged') {
            return [
                'name' => $interface,
                'nictype' => 'bridged',
                'parent' => 'nuber-bridged',
                'type' => 'nic'
            ];
        }

        return [
            'name' => $interface,
            'nictype' => 'bridged',
            'parent' => $network,
            'type' => 'nic'
        ];
    }

    /**
     * Adjusts the IP address used in the connect string e.g. tcp:{$instanceIp}:123
     *
     * @param array $devices
     * @param string $from
     * @param string $to
     * @return array
     */
    private function changeIpAddress(array $devices, string $from, string $to): array
    {
        foreach ($devices as &$device) {
            if ($device['type'] !== 'proxy') {
                continue;
            }
            $device['connect'] = str_replace($from, $to, $device['connect']); // tcp:{$instanceIp}:123
        }

        return $devices;
    }

    private function getNetworkInterface(array $networks) : string
    {
        foreach ($networks as $network) {
            if ($network['type'] === 'physical') {
                return $network['name'];
            }
        }
        throw new RuntimeException('No physical network card found');
    }
}
