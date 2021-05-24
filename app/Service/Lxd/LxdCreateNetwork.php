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
use App\Lxd\LxdClient;
use Origin\Service\Result;
use App\Service\ApplicationService;

/**
 * @see https://discuss.linuxcontainers.org/t/lxd-ipv6-networking-questions-novice/6961/2
 *
 * @method Result dispatch(string $network, bool $enabled)
 */
class LxdCreateNetwork extends ApplicationService
{
    use LxdTrait;

    private LxdClient $client;

    protected function initialize(LxdClient $client): void
    {
        $this->client = $client;
    }

    /**
     * Networks are always eth0
     *
     * @param string $name
     * @param string $ipv4 e.g. 10.0.2.1/24
     * @param string $ipv6 e.g. fd10:0:0:0::2/64
     * @return Result
     */
    protected function execute(string $name, string $ipv4 = null, string $ipv6 = null): Result
    {
        try {
            $this->client->network->create($name, [
                'description' => 'Nuber Virtual Network',
                'config' => [
                    'ipv4.address' => $ipv4 ?? 'none',
                    'ipv4.nat' => 'true',
                    'ipv6.address' => $ipv6 ?? 'none',
                    'ipv6.nat' => 'true'
                ]
            ]);
        } catch (Exception $exception) {
            return new Result($this->transformException($exception));
        }

        return new Result([
            'data' => []
        ]);
    }
}
