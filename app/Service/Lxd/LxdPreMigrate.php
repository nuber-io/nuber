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
use Origin\Text\Text;
use App\Lxd\LxdClient;
use Origin\Service\Result;
use App\Service\ApplicationService;
use App\Lxd\Endpoint\Host as HostEndpoint;
use App\Lxd\Endpoint\Instance as InstanceEndpoint;

/**
 * Before migration lets do some checks
 *
 * @method Result dispatch(string $instance, string $host)
 */
class LxdPreMigrate extends ApplicationService
{
    use LxdTrait;

    private LxdClient $client;
    
    protected function initialize(LxdClient $client): void
    {
        $this->client = $client;
    }

    /**
     * TODO: Add check architecture is same on both hosts.
     *
     * @param string $instance
     * @param string $host
     * @return Result
     */
    protected function execute(string $instance, string $host): Result
    {
        try {
            $info = (new HostEndpoint(['host' => $host]))->info();
            $remoteInstances = (new InstanceEndpoint(['host' => $host]))->list(['recursive' => 0]);
        } catch (Exception $exception) {
            return new Result([
                'error' => [
                    'message' => 'Error connecting to host',
                    'code' => $exception->getCode(),
                    'error' => $exception->getMessage()
                ]
            ]);
        }
        /**
         * Migrating a container that has a volume attached causes issues
         * e.g common start logic: Failed to start device "bsv1": No such object
        */
        if ($this->hasVolumes($instance)) {
            return new Result([
                'error' => [
                    'message' => 'Cannot migrate an instance with volumes attached',
                    'code' => 400
                ]
            ]);
        }

        if (! $this->versionCompatability($info)) {
            return new Result([
                'error' => [
                    'message' => 'Remote server is using an older version of LXD',
                    'code' => 400
                ]
            ]);
        }

        if (in_array($instance, $remoteInstances)) {
            return new Result([
                'error' => [
                    'message' => 'An instance already exists on the remote host with this name',
                    'code' => 400
                ]
            ]);
        }

        return new Result([
            'data' => []
        ]);
    }

    /**
     * @param string $instance
     * @return boolean
     */
    private function hasVolumes(string $instance): bool
    {
        $info = $this->client->instance->info($instance);

        foreach ($info['devices'] ?? [] as $device => $config) {
            if (Text::startsWith('bsv', $device)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $remoteInfo
     * @return boolean
     */
    private function versionCompatability(array $remoteInfo): bool
    {
        $currentVersion = $this->client->host->info()['environment']['server_version'];
        $remoteVersion = $remoteInfo['environment']['server_version'];

        return version_compare($this->version($remoteVersion), $this->version($currentVersion)) >= 0;
    }

    /**
     * @param String $version
     * @return string
     */
    private function version(string $version): string
    {
        $version = explode('.', $version);

        return "{$version[0]}.{$version[1]}";
    }
}
