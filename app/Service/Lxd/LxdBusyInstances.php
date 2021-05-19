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
 * @method Result dispatch()
 */
class LxdBusyInstances extends ApplicationService
{
    private LxdClient $client;

    /**
     * @param \App\Lxd\LxdClient $client
     * @return void
     */
    protected function initialize(LxdClient $client): void
    {
        $this->client = $client;
    }

    /**
     * @return \Origin\Service\Result
     */
    protected function execute(): Result
    {
        $operations = $this->client->operation->list(['recursive' => 2]);

        $instances = [];
        if (isset($operations['running'])) {
            foreach ($operations['running'] as $operation) {
                $containers = isset($operation['resources']['instances']) ? $operation['resources']['instances'] : [];
                foreach ($containers as $container) {
                    list(, $name) = explode('instances/', $container);
                    $instances[] = $name;
                }
            }
        }

        return new Result([
            'data' => $instances
        ]);
    }
}
