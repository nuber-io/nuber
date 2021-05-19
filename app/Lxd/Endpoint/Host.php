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
namespace App\Lxd\Endpoint;

use App\Lxd\Endpoint;

class Host extends Endpoint
{
    /**
     * Gets the certificate for this host
     *
     * @return string
     */
    public function certificate(): string
    {
        return $this->info()['environment']['certificate'];
    }
    
    /**
     * Gets the information about the host
     * @example lxc info
     *
     * @return array
     */
    public function info(): array
    {
        return $this->sendGetRequest('');
    }

    /**
     * @return array
     */
    public function resources(): array
    {
        return $this->sendGetRequest('/resources');
    }

    /**
     * Updates the server config or other properties
     *
     * @param array $options
     * @return void
     */
    public function update(array $options): void
    {
        $this->sendPatchRequest('/', [
            'data' => $options
        ]);
    }
}
