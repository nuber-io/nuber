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

use Origin\Log\Log;
use App\Lxd\LxdClient;
use Origin\Service\Result;
use App\Service\ApplicationService;

/**
 * Handles the logic for restoring a snapshot between different storage types.
 *
 * ZFS does not allow to restore from snapshots before the latest. This can also
 * be configured in LXD using lxc storage set default volume.zfs.remove_snapshots true
 *
 * @method Result dispatch(string $instance, string $snapshot)
 */
class LxdRestoreSnapshot extends ApplicationService
{
    private LxdClient $client;
    
    protected function initialize(LxdClient $client): void
    {
        $this->client = $client;
    }

    /**
     * @param string $instance
     * @param string $snapshot
     * @return Result
     */
    protected function execute(string $instance, string $snapshot): Result
    {
        $result = $this->removeSubsequentSnapshots($instance, $snapshot);
        if ($result instanceof Result) {
            return $result;
        }
        
        $response = $this->client->operation->wait(
            $this->client->snapshot->restore($instance, $snapshot)
        );

        if ($response['err']) {
            return new Result(['error' => [
                'message' => $response['err'],
                'code' => $response['status_code']
            ]]);
        }

        return new Result([]);
    }

    /**
     * ZFS does not allow for snapshots to be restored if there are subsequent snapshots.
     *
     * @param string $instance
     * @param string $except
     * @return \Origin\Service\Result|null
     */
    private function removeSubsequentSnapshots(string $instance, string $except)
    {
        foreach ($this->subsequentSnapshots($instance, $except) as $snapshot) {
            $response = $this->client->operation->wait(
                $this->client->snapshot->delete($instance, $snapshot)
            );

            if ($response['err']) {
                Log::error($response['err']);

                return new Result([
                    'error' => [
                        'message' => 'Error removing snapshots',
                        'code' => 500,
                        'reason' => $response['err']
                    ]
                ]);
            }
        }

        return null;
    }

    /**
     * Gets a list of snapshots after the provided one
     *
     * @param string $instance
     * @param string $name
     * @return array
     */
    private function subsequentSnapshots(string $instance, string $name): array
    {
        $out = [];
        $snapshots = $this->client->snapshot->list($instance, ['recursive' => 0]);

        $start = false;
        foreach ($snapshots as $snapshot) {
            if ($snapshot === $name) {
                $start = true;
                continue;
            }
            if ($start) {
                $out[] = $snapshot;
            }
        }

        return $out;
    }
}
