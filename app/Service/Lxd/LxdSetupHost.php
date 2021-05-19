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

use RuntimeException;
use App\Lxd\LxdClient;
use Origin\Service\Result;
use App\Service\ApplicationService;
use Origin\HttpClient\Exception\HttpException;

/**
 * This will:
 *
 * 1. Create the profiles that are used
 * 2. Create a nuberbridge network which share the same subnet so migration can work.
 *
 * @method Result dispatch(string $host)
 */
class LxdSetupHost extends ApplicationService
{
    use LxdTrait;

    private LxdClient $client;

    private array $defaultProfile = [
        'devices' => [
            'root' => [
                'path' => '/',
                'pool' => 'default',
                'type' => 'disk'
            ]
        ]
    ];

    /**
     * Setup this way so static IP addresses can be assigned
     *
     * $ lxc network attach lxdbr0 c1 eth0 eth0 -- debug
     * $ lxc config device set c1 eth0 ipv4.address 10.135.210.244 --debug
     */
    private array $natProfile = [
        'description' => 'Nuber NAT Network Profile',
        'devices' => [
            'eth0' => [
                'name' => 'eth0',
                'nictype' => 'bridged',
                'parent' => 'nuberbr0',
                'type' => 'nic'
            ]
        ]
    ];

    /**
     * This assumes bridge network is configured as `nuberbr1`
     * @internal virtualbox creates br0 by default but can't be used with this
     */
    private array $bridgedProfile = [
        'description' => 'Nuber Bridged Network Profile',
        'devices' => [
            'eth0' => [
                'name' => 'eth0',
                'nictype' => 'bridged',
                'parent' => 'nuberbr1',
                'type' => 'nic'
            ]
        ]
    ];

    /**
     * @see https://blog.simos.info/how-to-make-your-lxd-container-get-ip-addresses-from-your-lan/
     */
    private array $macvlanProfile = [
        'description' => 'Nuber Macvlan Profile',
        'devices' => [
            'eth0' => [
                'name' => 'eth0',
                'nictype' => 'macvlan',
                'parent' => null,
                'type' => 'nic'
            ]
        ]
    ];

    /**
     * @see https://en.wikipedia.org/wiki/Private_network
     */
    private array $privateNetwork = [
        'description' => 'Nuber Private Network',
        'config' => [
            'ipv4.address' => '10.0.0.1/24',
            'ipv4.nat' => 'true',
            'ipv6.address' => 'fd10:0:0:0::1/64', //  Unique local address (ULA)
            'ipv6.nat' => 'true'
        ]
    ];

    /**
     * @see https://lxd.readthedocs.io/en/latest/networks/
     * @see https://blog.simos.info/how-to-make-your-lxd-containers-get-ip-addresses-from-your-lan-using-a-bridge/
     * @see https://stgraber.org/2016/10/27/network-management-with-lxd-2-3/
     */
    private array $bridgedNetwork = [
        'description' => 'Nuber Bridged Network',
        'config' => [
            'nictype' => 'bridged',
            'parent' => 'nuberbr1',
            'type' => 'nic'
        ]
    ];

    /**
     * Main code
     *
     * @param string $host
     * @return Result
     */
    protected function execute(string $host): Result
    {
        $this->client = new LxdClient($host);

        try {
            $this->createNetworks();
            $this->createProfiles();
            $this->configureStorage();
        } catch (HttpException $exception) {
            return new Result(
                $this->transformException($exception)
            );
        }
      
        return new Result(['data' => []]);
    }

    /**
     * Configure the storage pools, if needed.
     *
     * @return void
     */
    private function configureStorage(): void
    {
        foreach ($this->client->storage->list() as $storage) {
            switch ($storage['driver']) {
                case 'zfs':
                    $this->setupZFS($storage);
                break;
                case 'btrfs':
                    $this->setupBTRFS($storage);
                break;
            }
        }
    }

    /**
     * Fix default values which just don't work
     * @see https://linuxcontainers.org/lxd/docs/master/server
     * @param array $storage
     * @return void
     */
    private function setupZFS(array $storage): void
    {
        $this->client->storage->update($storage['name'], [
            'config' => [
                'volume.zfs.remove_snapshots' => 'true', // ZFS does not allow to restore from snapshots before the latest
                'zfs.clone_copy' => 'false', // Don't want clones with snapshots to be linked
            ]
        ]);
    }

    /**
     * @see https://linuxcontainers.org/lxd/docs/master/server
     *
     * @param array $storage
     * @return void
     */
    private function setupBTRFS(array $storage): void
    {
        // anything needed here
    }

    /**
     * @return void
     */
    private function createNetworks(): void
    {
        $networks = $this->client->network->list(['recursive' => 0]);
        if (! in_array('nuberbr0', $networks)) {
            $this->client->network->create('nuberbr0', $this->privateNetwork);
        }
    }

    /**
     * @return void
     */
    private function createProfiles(): void
    {
        $profiles = $this->client->profile->list(['recursive' => 0]);
        if (! in_array('nuber-default', $profiles)) {
            $this->client->profile->create('nuber-default', $this->defaultProfile);
        }

        if (! in_array('nuber-nat', $profiles)) {
            $this->client->profile->create('nuber-nat', $this->natProfile);
        }

        if (! in_array('nuber-bridged', $profiles)) {
            $this->client->profile->create('nuber-bridged', $this->bridgedProfile);
        }

        if (! in_array('nuber-macvlan', $profiles)) {
            $this->client->profile->create('nuber-macvlan', $this->macvlanProfile());
        }
    }

    /**
     * Gets the macvlan profile which needs
     *
     * @return array
     */
    private function macvlanProfile() : array
    {
        $profile = $this->macvlanProfile;
        $profile['devices']['eth0']['parent'] = $this->getNetworkInterface($this->client->network->list());

        return $profile;
    }

    /**
    * Find the first physical network interface
    *
    * @param array $networks
    * @return string
    */
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
