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
 * Creates the instance using a local image and assigns a static IP address. The instance
 * will have two devices, eth0 & root
 *
 * @method Result dispatch(string $name, string $fingerprint, string $memory, string $disk, string $cpu)
 */
class LxdCreateInstance extends ApplicationService
{
    private LxdClient $client;
    
    protected function initialize(LxdClient $client): void
    {
        $this->client = $client;
    }
    /**
     * Creates a new Linux Container Instance
     *
     * @param string $name
     * @param string $fingerprint
     * @param string $memory
     * @param string $disk
     * @param integer $cpu
     * @return \Origin\Service\Result|null
     */
    protected function execute(string $name, string $fingerprint, string $memory, string $disk, string $cpu): ?Result
    {
        $uuid = $this->client->instance->create($fingerprint, $name, [
            'profiles' => [
                'nuber-default',
                'nuber-nat'
            ],
            'config' => [
                'limits.memory' => $memory,
                'limits.cpu' => (string) $cpu
            ]
        ]);

        $response = $this->client->operation->wait($uuid);

        if (! empty($response['err'])) {
            return new Result([
                'success' => false,
                'error' => [
                    'message' => $response['err'],
                    'code' => $response['status_code'],
                ]
            ]);
        }

        // Needs to set after instance created
        $this->client->device->set($name, 'root', 'size', $disk);

        return (new LxdStartInstance($this->client))->dispatch($name, true);
    }
}
