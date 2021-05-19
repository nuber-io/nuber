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

use Origin\Service\Result;
use Origin\HttpClient\Http;
use App\Service\ApplicationService;

class LxdRemoteImageList extends ApplicationService
{
    private Http $http;
    /**
    * Dependencies will be sent here from constructor
    */
    protected function initialize(): void
    {
        $this->http = new Http([
            'base' => 'https://images.linuxcontainers.org:8443',
            'curl' => [
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false
            ],
            'type' => 'json'
        ]);
    }

    /*
    * Service logic goes here and return a result object or null
    */
    protected function execute(): ?Result
    {
        $out = [];
        $response = $this->http->get('/1.0/images/aliases');

        if ($response->ok()) {
            foreach ($response->json()['metadata'] as $url) {
                $out[] = substr($url, 20);
            }

            return $this->result([
                'success' => true,
                'data' => $out
            ]);
        }

        return $this->result([
            'success' => false,
            'error' => [
                'message' => $response->body(),
                'code' => $response->statusCode()
            ]
        ]);
    }
}
