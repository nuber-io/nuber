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
use App\Lxd\Endpoint\Image;
use App\Service\ApplicationService;

class LxdImageDownload extends ApplicationService
{
    private LxdClient $client;
    
    protected function initialize(LxdClient $client): void
    {
        $this->client = $client;
    }

    /**
     * This is for downloading an image and adding to local
     * store.
     *
     * @param string $image 6b4ffc853d10

     * @return Result|null
     */
    protected function execute(string $image, string $alias = null): ?Result
    {

        // Config::read('App.imageDownloadTimeout') //

        $response = $this->client->operation->wait(
            $this->client->image->fetch($image, [
                'alias' => $alias
            ])
        );
      
        if ($response['status_code'] === 200) {
            return $this->result([
                'data' => $response['metadata']
            ]);
        }
        
        return $this->result([
            'error' => [
                'message' => $response['err'],
                'code' => $response['status_code']
            ]]);
    }
}
