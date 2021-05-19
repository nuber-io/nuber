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
use Origin\Text\Text;
use App\Lxd\Endpoint\Exception\NotFoundException;

/**
 * [X] attach         Attach new storage volumes to instances
 * [ ] attach-profile Attach new storage volumes to profiles
 * [ ] copy           Copy storage volumes
 * [X] create         Create new custom storage volumes
 * [X] delete         Delete storage volumes
 * [X] detach         Detach storage volumes from instances
 * [ ] detach-profile Detach storage volumes from profiles
 * [ ] edit           Edit storage volume configurations as YAML
 * [ ] get            Get values for storage volume configuration keys
 * [X] list           List storage volumes
 * [ ] move           Move storage volumes between pools
 * [ ] rename         Rename storage volumes and storage volume snapshots
 * [ ] restore        Restore storage volume snapshots
 * [ ] set            Set storage volume configuration keys
 * [ ] show           Show storage volum configurations
 * [ ] snapshot       Snapshot storage volumes
 * [ ] unset          Unset storage volume configuration keys
 */
class Volume extends Endpoint
{
    /**
     * Gets a list of volumes
     *
     * $ lxc storage volume list default
     *
     * @param array $options
     *  - pool: storage pool to use, set to default.
     *  - recursive: default 1. levels of recursion
     * @return array
     */
    public function list(array $options = []): array
    {
        $options += ['pool' => 'default', 'recursive' => 1, 'type' => 'custom'];

        $endpoint = "/storage-pools/{$options['pool']}/volumes/{$options['type']}";

        $response = $this->sendGetRequest($endpoint, [
            'query' => [
                'recursion' => $options['recursive']
            ]
        ]);

        /**
        * Removing endpoint completley makes it impossible to identify. So if
        * type set to null, you will get something like `container/c1/snapshots/c1-1591353284`
        *
        * Array
        * (
        *     [0] => /1.0/storage-pools/default/volumes/container/c1
        *     [1] => /1.0/storage-pools/default/volumes/container/c1/snapshots/c1-1591353284
        *     [2] => /1.0/storage-pools/default/volumes/custom/foo
        *     [3] => /1.0/storage-pools/default/volumes/custom/volume-test
        *     [4] => /1.0/storage-pools/default/volumes/image/4542f9a20469f7e2e241a51941f3302ecc9f621e5e7649a521c290f52c640f68
        * )
        */
 
        foreach ([$endpoint, '/1.0/instances/','/1.0/images/'] as $needle) {
            $response = $this->removeEndpoints($response, $needle);
        }

        /**
        * clean up snapshots
        * snapshots can be identified because name key has / e.g. c1/c1-1591353284
        */
        foreach ($response as &$value) {
            if (is_string($value)) {
                $value = substr($value, strrpos($value, '/') + 1);
                continue;
            }
            foreach ($value['used_by'] as &$usedBy) {
                if (Text::contains('/snapshots/', $usedBy)) {
                    $usedBy = Text::right('/snapshots/', $usedBy);
                }
            }
        }

        return $response;
    }

    /**
     * Creates a volume
     *
     * @see https://lxd.readthedocs.io/en/latest/rest-api/#10storage-pools
     * @param string $name
     * @param array $options The following options keys are supported
     *  - pool: storage pool to use, set to default.
     *  - size: default:10GB For block storage pools LVM/CEPHFS size must be provided or LXD will set its own
     * default, also at 10GB.
     *  - driver: default:zfs available: lvm zfs ceph btrfs cephfs dir
     * @return void
     */
    public function create(string $name, array $options = []): void
    {
        $options += ['pool' => 'default', 'size' => '10GB','driver' => 'zfs'];
     
        $this->sendPostRequest("/storage-pools/{$options['pool']}/volumes", [
            'data' => [
                'name' => $name,
                'type' => 'custom',
                'content_type' => 'filesystem',
                'config' => [
                    'size' => $options['size'],
                ],
                'driver' => $options['driver']
            ]
        ]);
    }

    /**
     * Gets information on a volume
     *
     * @param string $name
     * @param array $options
     *  - pool: storage pool to use, set to default.
     *  - type: default:custom
     * @return array
     */
    public function get(string $name, array $options = []): array
    {
        $options += ['pool' => 'default', 'type' => 'custom'];

        return $this->sendGetRequest("/storage-pools/{$options['pool']}/volumes/{$options['type']}/{$name}");
    }

    /**
      * Deletes a volume
      *
      * @param string $name
      * @param array $options
      *  - type: default:custom
      * @return void
      */
    public function delete(string $name, array $options = []): void
    {
        $options += ['pool' => 'default', 'type' => 'custom'];

        $this->sendDeleteRequest("/storage-pools/{$options['pool']}/volumes/{$options['type']}/{$name}");
    }

    /**
     * Renames a volume
     *
     * @internal moving to a different pool will be an async response
     *
     * @param string $name
     * @param string $newname
     * @param array $options
     *  - type: default:custom
     * @return void
     */
    public function rename(string $name, string $newname, array $options = []): void
    {
        $options += ['pool' => 'default', 'type' => 'custom'];

        $this->sendPostRequest("/storage-pools/{$options['pool']}/volumes/{$options['type']}/{$name}", [
            'data' => [
                'name' => $newname
            ]
        ]);
    }

    /**
      * Attaches a volume to an instance
      *
      * @param string $name
      * @param string $instance
      * @param string $device bsv1
      * @param string $path /mnt/blockstorage
      * @param array $options
      * @return string
      */
    public function attach(string $name, string $instance, string $device, string $path, array $options = []): string
    {
        $options += ['pool' => 'default'];

        /**
        * Check volume exists
        */
        $this->get($name);

        $info = $this->instance->info($instance);

        $info['devices'][$device] = [
            'path' => $path,
            'pool' => $options['pool'],
            'source' => $name,
            'type' => 'disk'
        ];

        $response = $this->sendPutRequest("/instances/{$instance}", [
            'data' => [
                'architecture' => $info['architecture'],
                'config' => $info['config'],
                'devices' => $info['devices'],
                'ephemeral' => $info['ephemeral'],
                'profiles' => $info['profiles'],
                'stateful' => $info['stateful'],
                'description' => $info['description']
            ]
        ]);

        return $response['id'];
    }

    /**
      * Attaches a volume to an instance
      *
      * @param string $name
      * @param string $instance
      * @param string $device bsv1
      * @param array $options
      * @return string
      */
    public function detach(string $instance, string $device, array $options = []): string
    {
        $options += ['pool' => 'default'];

        $info = $this->instance->info($instance);

        if (! isset($info['devices'][$device])) {
            throw new NotFoundException('Device Not Found');
        }

        unset($info['devices'][$device]);
      
        $response = $this->sendPutRequest("/instances/{$instance}", [
            'data' => [
                'architecture' => $info['architecture'],
                'config' => $info['config'],
                'devices' => empty($info['devices']) ?  null : $info['devices'], // go lang friendly
                'ephemeral' => $info['ephemeral'],
                'profiles' => $info['profiles'],
                'stateful' => $info['stateful'],
                'description' => $info['description']
            ]
        ]);

        return $response['id'];
    }
}
