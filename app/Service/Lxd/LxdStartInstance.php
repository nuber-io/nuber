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
use App\Lxd\Endpoint\Exception\NotFoundException;

/**
 * Been experiencing issues with BTRFS, apply qutoa immediately after creating a container and
 * trying to start the container gives issues. Also despite LXD starting a container there is a small
 * delay after it starts before the network is ready, so this needs to be waited for.
 *
 * This service is a layer over over LXD with restart attempts and waiting for network
 *
 * @method Result dispatch(string $name)
 */
class LxdStartInstance extends ApplicationService
{
    private LxdClient $client;

    /**
     * How many times to retry if the instance fails to start
     */
    const RETRY = 3;

    /**
     * How long to wait before each attempt.
     * Initially this was set to 5, but when creating a new container or restoring a backup on BTRFS using
     * quotas this still does not work all the time. Due to temorary quota
     */
    const WAIT = 10;
    
    protected function initialize(LxdClient $client): void
    {
        $this->client = $client;
    }

    /**
     * @param string $name
     * @return Result
     */
    protected function execute(string $name, bool $create = false): Result
    {
        try {
            $state = $this->client->instance->state($name);
        } catch (NotFoundException $exception) {
            return new Result([
                'error' => ['message' => 'Instance not found', 'code' => 404]
            ]);
        }

        if ($state['status'] === 'Running') {
            return new Result([
                'error' => ['message' => 'Instance already running', 'code' => 400]
            ]);
        }
        
        /**
         * Issues with BTRFS, quotas and starting newly created or restored containers.
         * e.g. Failed preparing container for start: Failed to create file \"/var/snap/lxd/common/lxd/containers/ubuntu/backup.yaml\": open /var/snap/lxd/common/lxd/containers/ubuntu/backup.yaml: disk quota exceeded
         * Solution: Increase the wait before retrying to 10 seconds seems to work however I am adding an initial
         * delay
         */
        if ($create && $this->isBTRFS($name)) {
            sleep(10);
        }

        // Start the starter
        $response = $this->startInstance($name);
        if ($response['err']) {
            return new Result([
                'error' => [
                    'message' => 'Error starting instance',
                    'code' => 500,
                    'error' => $response['err']
                ]
            ]);
        }

        // Now wait for network to boot
        $device = $this->waitForNetworkToStart($name);
  
        if (! $device) {
            return new Result([
                'error' => [
                    'message' => 'Network not running',
                    'code' => 500
                ]
            ]);
        }
    
        return new Result([
            'data' => [
                'name' => $name,
                'ip_address' => $device['address']
            ]
        ]);
    }

    /**
     * Check the expanded devices section for containers created from command line without
     * the devices being overwitten.
     *
     * @return boolean
     */
    private function isBTRFS(string $name): bool
    {
        $result = $this->client->instance->info($name);
        if (! empty($result['expanded_devices']['root']['pool'])) {
            $storage = $this->client->storage->get($result['expanded_devices']['root']['pool']);

            return $storage['driver'] === 'btrfs';
        }

        return false;
    }

    /**
     * Automatic starteer
     *
     * @param string $name
     * @return array
     */
    private function startInstance(string $name): array
    {
        $attempts = 0;
        while ($attempts < self::RETRY) {
            $response = $this->client->operation->wait(
                $this->client->instance->start($name)
            );

            if (empty($response['err'])) {
                break;
            }
            $attempts++;
            sleep(self::WAIT);
        }

        return $response;
    }

    /**
     * Networking can vary between host, OS etc
     * Two networks with the same IP range will cause this to break.
     * @param string $name
     * @return array|null $device
     */
    private function waitForNetworkToStart(string $name): ?array
    {
        $attempts = 0;
        $device = null;
        while (! $device && $attempts < 5) {
            $device = $this->getNetworkDevice($name);
            $attempts++;
            sleep(1);
        }

        return $device;
    }

    /**
     * Finds the device with the IP address
     *
     * @param string $name
     * @return array|null
     */
    private function getNetworkDevice(string $name): ?array
    {
        $state = $this->client->instance->state($name);

        $haystack = $state['network']['eth0']['addresses'] ?? [];

        return $this->searchArray('family', 'inet', $haystack);
    }

    /**
     * @param string $key
     * @param string $value
     * @param array $haystack
     * @return array|null
     */
    private function searchArray(string $key, string $value, array $haystack): ?array
    {
        foreach ($haystack as $item) {
            if (isset($item[$key]) && $item[$key] === $value) {
                return $item;
            }
        }

        return null;
    }
}
