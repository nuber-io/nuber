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
use RuntimeException;

use Origin\Filesystem\Folder;
use Origin\Security\Security;
use function Origin\Defer\defer;

class Backup extends Endpoint
{
    /**
     * Gets a list of backups for an instance
     *
     * @param string $instance
     * @param array $options
     *  - recursive: levels of recursion
     * @return array
     */
    public function list(string $instance, array $options = []): array
    {
        $options += ['recursive' => 1];
        $response = $this->sendGetRequest("/instances/{$instance}/backups", [
            'query' => [
                'recursion' => $options['recursive']
            ]
        ]);

        return $this->removeEndpoints(
            $response,
            "/1.0/instances/{$instance}/backups/"
        );
    }

    /**
     * Gets information on an backup
     *
     * @param string $instance
     * @param string $name name of backup
     * @return array
     */
    public function get(string $instance, string $name): array
    {
        return $this->sendGetRequest("/instances/{$instance}/backups/{$name}");
    }

    /**
     * Exports a backup as tarball, these can only be imported lxc import (dont confuse with lxd import).
     *
     * Trying to import a backup from a different storage driver will fail.
     * e.g Error: Optimized backup storage driver "btrfs" differs from the target storage pool driver "zfs"
     *
     * @param string $instance
     * @param string $name
     * @return string $file the file with full path
     */
    public function export(string $instance, string $name): string
    {
        $folder = sys_get_temp_dir() . '/lxd/backups';
        if (! Folder::exists($folder) && ! Folder::create($folder, ['recursive' => true])) {
            throw new RuntimeException('Error creating ' . $folder);
        }
      
        $file = $folder . '/' . Security::uuid(['macAddress' => true]) . '.tar.gz';

        $fp = fopen($file, 'w');
        defer($void, 'fclose', $fp);
            
        $curlConfig = $this->curlConfig;
        $curlConfig[CURLOPT_HEADER] = false;
        $curlConfig[CURLOPT_FILE] = $fp;

        $backup = (int) ini_get('max_execution_time');
        set_time_limit(0);
        $this->sendGetRequest("/instances/{$instance}/backups/{$name}/export", [
            'curl' => $curlConfig,
            'timeout' => 0
        ]);

        set_time_limit($backup);

        return $file;
    }

    /**
     * Creates a backup
     *
     * $ curl -k --cert /var/www/config/certs/certificate --key /var/www/config/certs/privateKey \
     *   https://192.168.1.100:8443/1.0/instances/c1/backups/c1-20210219
     *
     * @param string $instance
     * @param string $name name of backup
     * @param array $options
     *  - expires : miliseconds to expiry
     *  - snapshots: default:false include snapshots
     *  - optimize: default: false. if true for BTRFS send or ZFS send is used. If using optimized storage then
     *              backups can only be restored on a host using the same storage driver, if not restoration will
     *              fail.
     * @return string
     */
    public function create(string $instance, string $name, array $options = []): string
    {
        $options += ['expires' => null, 'snapshots' => false, 'optimize' => false];

        $fields = [
            'name' => $name,
            'expiry' => $options['expires'],
            'instance_only' => ! $options['snapshots'],
            'optimized_storage' => $options['optimize']
        ];

        $response = $this->sendPostRequest("/instances/{$instance}/backups", ['data' => $fields]);

        return $response['id'];
    }

    /**
     * Imports an instance from a backup, this can't have the same name.
     *
     *    curl -X POST -k -H "Content-Type: application/octet-stream" \
     *       --data-binary @tmp.gz --cert /var/www/config/certs/certificate \
     *       --key /var/www/config/certs/privateKey https://192.168.1.160:8443/1.0/ \
     *       -H "X-LXD-name: c1"
     *
     * The overwriting the name is only available in LXD 4.7 and requires to switch snap channels
     * $ snap refresh lxd --channel=latest/stable
     *
     */
    public function import(string $path)
    {
        $fp = fopen($path, 'r');
        defer($void, 'fclose', $fp);

        $curlConfig = $this->curlConfig;
        $curlConfig[CURLOPT_INFILE] = $fp;
        
        $backup = (int) ini_get('max_execution_time');

        set_time_limit(0);
        $response = $this->sendPostRequest('/instances', [
            'type' => 'none',
            'timeout' => 0,
            'headers' => [
                'Content-Type' => 'application/octet-stream'
            ],
            'curl' => $curlConfig
        ]);
        set_time_limit($backup);

        return $response['id'];
    }

    /**
     * Deletes a backup
     *
     * @param string $instance
     * @param string $name
     * @return string
     */
    public function delete(string $instance, string $name): string
    {
        $response = $this->sendDeleteRequest("/instances/{$instance}/backups/{$name}");

        return $response['id'];
    }

    /**
     * Renames a backup
     *
     * @param string $instance
     * @param string $name
     * @param string $newName
     * @return void
     */
    public function rename(string $instance, string $name, string $newName): void
    {
        $this->sendPostRequest("/instances/{$instance}/backups/{$name}", [
            'data' => [
                'name' => $newName,
            ]
        ]);
    }
}
