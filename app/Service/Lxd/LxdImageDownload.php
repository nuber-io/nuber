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
     * @param string $image alpine/3.10/amd64

     * @return Result|null
     */
    protected function execute(string $image): ?Result
    {
        $response = $this->client->operation->wait(
            $this->client->image->fetch($image, ['alias' => $image])
        );
      
        if ($response['status_code'] === 200) {
            return $this->result([
                'success' => true,
                'data' => $response['metadata']
            ]);
        }
        
        return $this->result([
            'success' => false,
            'error' => [
                'message' => $response['err'],
                'code' => $response['status_code']
            ]]);
    }
}
