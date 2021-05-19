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
use Origin\Service\Service;

/**
 * @method Result dispatch(string $host, string $instance, string $name)
 */
class LxdCloneInstance extends Service
{
    private LxdClient $client;
    
    protected function initialize(LxdClient $client): void
    {
        $this->client = $client;
    }

    protected function execute(string $instance, string $name): ?Result
    {
        $payload = ['data' => []];

        try {
            $result = $this->client->operation->wait(
                $this->client->instance->copy($instance, $name)
            );

            // remove proxies
            if ($result['err']) {
                throw new Exception($result['err']);
            }

            // convert the proxies over
            $result = (new LxdConvertProxies($this->client))->dispatch($name);

            if ($result->error()) {
                $payload = $result->error();
            }
        } catch (Exception $exception) {
            $payload = [
                'error' => [
                    'message' => $exception->getMessage(),
                    'code' => $exception->getCode()
                ]
            ];
        }

        return new Result($payload);
    }
}
