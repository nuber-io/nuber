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
use Origin\HttpClient\Exception\HttpException;

/**
 * This will:
 *
 * 1. Create the profiles that are used
 * 2. Create a nuberbridge network which share the same subnet so migration can work.

 * @see https://lxd.readthedocs.io/en/latest/networks/
 * @see https://blog.simos.info/how-to-make-your-lxd-containers-get-ip-addresses-from-your-lan-using-a-bridge/
 * @see https://stgraber.org/2016/10/27/network-management-with-lxd-2-3/
 *
 *  @method Result dispatch(string $host)
 */
class LxdSetupHost extends ApplicationService
{
    use LxdTrait;

    private LxdClient $client;

    /**
     * @see https://en.wikipedia.org/wiki/Private_network
     */
    private array $privateNetwork = [
        'description' => NUBER_VIRTUAL_NETWORK, #! important
        'config' => [
            'ipv4.address' => '10.0.0.1/24',
            'ipv4.nat' => 'true',
            'ipv6.address' => 'none',
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
        if (! in_array('vnet0', $networks)) {
            $this->client->network->create('vnet0', $this->privateNetwork);
        }
    }
}
