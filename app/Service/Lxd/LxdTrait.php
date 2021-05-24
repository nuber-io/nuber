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

trait LxdTrait
{
    /**
    * Converts an exception into a payload
    *
    * @param Exception $exception
    * @return array
    */
    protected function transformException(Exception $exception): array
    {
        return [
            'error' => [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode()
            ]
        ];
    }

    /**
    * Even so much as a sniff of a problem throw an exception since instance will
    * be deleted after copy.
    *
    * @param \App\Lxd\LxdClient $client
    * @param string $uuid
    * @return array
    */
    protected function backgroundOperation(LxdClient $client, string $uuid): array
    {
        $response = $client->operation->wait($uuid);
        if ($response['err'] || $response['status'] !== 'Success') {
            throw new Exception($response['err'] ?? 'Unkown error');
        }

        return $response;
    }
}
