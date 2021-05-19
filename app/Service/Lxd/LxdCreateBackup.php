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
 *
 * @method Result dispatch(string $name)
 */
class LxdCreateBackup extends ApplicationService
{
    private LxdClient $client;

    /**
     * Includes snapshots in backups
     */
    const INCLUDE_SNAPSHOTS = false;

    /**
     * If this setting is enabled backups cannot be restored on a server with a different storage pool.
     * Also the backup is binary, it is created with zfs send or btrs send.
     *
     * Backups will get dumped to /var/snap/lxd/common/lxd/backups before creating the tarball. You can configure
     * the server to use volumes without sizes on DIR/ BTRFS, and ZFS, but block storage like LVM and CEPH require
     * a size to be set.
     *
     * To create a volume and have backups go there - i think needs to be done from the start
     *
     * $ lxc storage volume create default backups
     * $ lxc config set storage.backups_volume=default/nuber-backups
     */
    const OPTIMIZE_STORAGE = true;

    protected function initialize(LxdClient $client): void
    {
        $this->client = $client;
    }

    /**
     * @param string $name
     * @return Result
     */
    protected function execute(string $instance, string $name): Result
    {
        $response = $this->client->operation->wait(
            $this->client->backup->create($instance, $name, [
                'snapshots' => self::INCLUDE_SNAPSHOTS,
                'optimize' => self::OPTIMIZE_STORAGE  # Important ensures recovery can be done using any storage driver
            ])
        );

        if (empty($response['err'])) {
            return new Result(['data' => []]);
        }

        return new Result([
            'error' => [
                'message' => $response['err'],
                'code' => $response['status_code']
            ]
        ], $response['status_code']);
    }
}
