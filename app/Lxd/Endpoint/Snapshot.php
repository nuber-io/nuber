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

class Snapshot extends Endpoint
{
    /**
     * Gets a list of snapshots
     *
     * @param string $name instance name
     * @param array $options
     *  - recursive: default 1. levels of recursion
     * @return array
     */
    public function list(string $name, array $options = []): array
    {
        $options += ['recursive' => 1];
        $response = $this->sendGetRequest("/instances/{$name}/snapshots", [
            'query' => [
                'recursion' => $options['recursive']
            ]
        ]);
    
        return $this->removeEndpoints(
            $response,
            "/1.0/instances/{$name}/snapshots/"
        );
    }

    /**
    * Gets information on a snapshot
    *
    * @param string $instance
    * @param string $name name of backup
    * @return array
    */
    public function get(string $instance, string $name): array
    {
        return $this->sendGetRequest("/instances/{$instance}/snapshots/{$name}");
    }

    /**
     * Take a snapshot
     *
     * @param string $instance
     * @param string $snapshotName
     * @param array $options
     *  - stateful: Whether to include state too
     * @return string $uuid
     */
    public function create(string $instance, string $snapshotName, array $options = []): string
    {
        $options += ['stateful' => false];
        $options['name'] = $snapshotName;
        $response = $this->sendPostRequest("/instances/{$instance}/snapshots", ['data' => $options]);

        return $response['id'];
    }

    /**
     * Restores a snapshot
     *
     * @param string $instance
     * @param string $snapshotName
     * @return string $uuid
     */
    public function restore(string $instance, string $snapshotName): string
    {
        $response = $this->sendPutRequest("/instances/{$instance}", [
            'data' => ['restore' => $snapshotName]
        ]);

        return $response['id'];
    }

    /**
     * Deletes a snapshot
    *
     * @param string $instance
     * @param string $snapshot
     * @return string
     */
    public function delete(string $instance, string $snapshot): string
    {
        $result = $this->sendDeleteRequest("/instances/{$instance}/snapshots/{$snapshot}");

        return $result['id'];
    }
}
