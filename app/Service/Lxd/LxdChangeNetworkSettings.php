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
    protected function execute(string $instance, string $eth0, string $eth1 = null): Result
    {
        try {
            $info = $this->client->instance->info($instance);

            $ip4Address = $ip6Address = null;
            // Remove second interface if it is there
            if (! empty($info['expanded_devices']['eth1'])) {
                unset($info['devices']['eth1']);
                $this->client->device->remove($instance, 'eth1');
            }
                    
            // reset the profiles
            $info['profiles'] = ['nuber-default', $eth0];
           
            // backup virtual network IP address
            $parent = $info['devices']['eth0']['parent'] ?? null;
            if (in_array($parent, ['nuberbr0','lxdbr0']) && $eth0 === 'nuber-nat') {
                $ip4Address = $info['devices']['eth0']['ipv4.address'] ?? null;
                $ip6Address = $info['devices']['eth0']['ipv6.address'] ?? null;
            }

            // set first interface settings and static address
            $info['devices']['eth0'] = $this->getDeviceConfig($eth0);
            if ($eth0 === 'nuber-nat') {
                $info['devices']['eth0']['ipv4.address'] = $ip4Address;
                $info['devices']['eth0']['ipv6.address'] = $ip6Address;
            }

            // set second interface
            if ($eth1) {
                $info['profiles'][] = $eth1;
                $info['devices']['eth1'] = $this->getDeviceConfig($eth1);
                $info['devices']['eth1']['name'] = 'eth1'; // rename
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

    private function getDeviceConfig(string $profile) : array
    {
        $profileInfo = $this->client->profile->get($profile);

        return $profileInfo['devices']['eth0'];
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
}
