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
use InvalidArgumentException;

class Network extends Endpoint
{

    /**
     * Validates a name
     *
     * @param string $name
     * @return void
     */
    private function validateName(string $name): void
    {
        if (! preg_match('/^[a-z][a-z0-9-]{1,14}$/i', $name)) {
            throw new InvalidArgumentException('Invalid network name');
        }
    }
    /**
     * Undocumented function
     *
     * @param array $options
     * @return array
     */
    public function list(array $options = []): array
    {
        $options += ['recursive' => 1];
        $response = $this->sendGetRequest('/networks', [
            'query' => [
                'recursion' => $options['recursive']
            ]
        ]);
       
        return $this->removeEndpoints(
            $response,
            '/1.0/networks/'
        );
    }

    /**
      * Gets information on the certificate
      *
      * @param string $name
      * @return array
      */
    public function get(string $name): array
    {
        $response = $this->sendGetRequest("/networks/{$name}");
        
        // Dont remove profiles endpoint, because instances can be here too. TODO: how to deal
        return $this->removeEndpoints(
            $response,
            '/1.0/instances/' // used_by
        );
    }
 
    /**
     * Creates a network
     *
     * @return void
     */
    public function create(string $name, array $options = []): void
    {
        $this->validateName($name);
        $this->sendPostRequest('/networks', [
            'data' => array_merge(['name' => $name], $options)
        ]);
    }

    /**
     * Renames a network
     *
     * @param string $name
     * @param string $newName
     * @return void
     */
    public function rename(string $name, string $newName): void
    {
        $this->validateName($newName);
        $this->sendPostRequest('/networks' ."/{$name}", [
            'data' => [
                'name' => $newName,
            ]
        ]);
    }

    /**
     * Edits the description or target of alias
     *
     * @param string $name
     * @param array $options
     * @return void
     */
    public function edit(string $name, array $options = []): void
    {
        $this->sendPatchRequest("/networks/{$name}", [
            'data' => $options
        ]);
    }
    
    /**
    *  Deletes a network
    *
    * @param string $name
    * @return void
    */
    public function delete(string $name): void
    {
        $this->sendDeleteRequest("/networks/{$name}");
    }
}
