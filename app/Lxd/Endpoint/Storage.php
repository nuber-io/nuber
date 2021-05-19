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
namespace App\Lxd\Endpoint;

use App\Lxd\Endpoint;

/**
 * This is not an lxd command,
 *
 * [ ] create      Create storage pools
 * [X] delete      Delete storage pools
 * [ ] edit        Edit storage pool configurations as YAML
 * [ ] get         Get values for storage pool configuration keys
 * [X] info        Show useful information about storage pools
 * [X] list        List available storage pools
 * [ ] set         Set storage pool configuration keys
 * [ ] show        Show storage pool configurations and resources
 * [ ] unset       Unset storage pool configuration keys
 * [ ] volume      Manage storage volumes
 *
 * @internal Instances see significant benefits from a copy-on-write filesystem like ZFS
 */
class Storage extends Endpoint
{
    /**
     * Gets a list of storage pools
     *
     * @param array $options
     *  - recursive: default 1. levels of recursion
     * @return array
     */
    public function list(array $options = []): array
    {
        $options += ['recursive' => 1];
        $response = $this->sendGetRequest('/storage-pools', [
            'query' => [
                'recursion' => $options['recursive']
            ]
        ]);

        return $this->removeEndpoints(
            $response,
            '/1.0/storage-pools/'
        );
    }

    /**
     * Creates a storage pool (Seems to be synchronous)
     *
     * @param string $name
     * @param array $options The following options keys are supported
     *  - size: 10GB
     *  - driver: default:zfs dir,lvm,zfs,ceph,btrfs
     * @return void
     */
    public function create(string $name, array $options = []): void
    {
        $options += ['size' => '10GB','driver' => 'zfs'];
     
        $this->sendPostRequest('/storage-pools', [
            'data' => [
                'name' => $name,
                'driver' => $options['driver'],
                'config' => [
                    'size' => $options['size'],
                ]
            ]
        ]);
    }

    /**
     * Gets information on storage
     *
     * @example
     *  $ lxc storage info default
     *
     * @param string $name
     * @return array
     */
    public function get(string $name): array
    {
        return $this->sendGetRequest("/storage-pools/{$name}");
    }

    /**
     * Updates a storage pool
     *
     * @param string $name
     * @param array $options
     * @return void
     */
    public function update(string $name, array $options): void
    {
        $this->sendPatchRequest("/storage-pools/{$name}", [
            'data' => $options
        ]);
    }

    /**
    * Deletes a storage pool
    *
    * @param string $name fingerprint
    * @return array
    */
    public function delete(string $name): void
    {
        $this->sendDeleteRequest("/storage-pools/{$name}");
    }
}
