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
 * @link https://github.com/lxc/lxd/blob/master/shared/osarch/architectures.go
 *
 * @method Result dispatch()
 */
class LxdArchitecture extends ApplicationService
{
    private LxdClient $client;

    protected function initialize(LxdClient $client): void
    {
        $this->client = $client;
    }

    protected function execute(): Result
    {
        $info = $this->client->host->info();

        $architectures = $info['environment']['architectures'];
        
        // not sure about this
        $architecture = 'i386';
        if (in_array('x86_64', $architectures)) {
            $architecture = 'amd64';
        } elseif (in_array('aarch64', $architectures)) {
            $architecture = 'arm64';
        }
        
        return new Result([
            'data' => [
                'architecture' => $architecture
            ]
        ]);
    }
}
