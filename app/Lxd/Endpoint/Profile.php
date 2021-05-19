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

/**
 * [ ] create      Create aliases for existing images
 * [ ] add         Add profiles to instances
 * [ ] assign      Assign sets of profiles to instances
 * [ ] copy        Copy profiles
 * [X] create      Create profiles
 * [X] delete      Delete profiles
 * [ ] device      Manage instance devices
 * [X] edit        Edit profile configurations as YAML
 * [ ] get         Get values for profile configuration keys
 * [X] list        List profiles
 * [ ] remove      Remove profiles from instances
 * [ ] rename      Rename profiles
 * [ ] set         Set profile configuration keys
 * [ ] show        Show profile configurations
 * [ ] unset       Unset profile configuration keys
 */
class Profile extends Endpoint
{
    /**
     * Gets a list of profiles
     *
     * @param array $options
     *  - recursive: default: 1. levels of recrusion.
     * @return array
     */
    public function list(array $options = []): array
    {
        $options += ['recursive' => 1];
        $response = $this->sendGetRequest('/profiles', [
            'query' => [
                'recursion' => $options['recursive']
            ]
        ]);
        
        return $this->removeEndpoints(
            $response,
            '/1.0/profiles/'
        );
    }

    /**
      * Gets information on a profile
      *
      * @param string $name
      * @return array
      */
    public function get(string $name): array
    {
        $response = $this->sendGetRequest("/profiles/{$name}");

        return $this->removeEndpoints(
            $response,
            '/1.0/profiles/'
        );
    }
 
    /**
     * Creates a profile
     *
     * @return void
     */
    public function create(string $name, array $options = []): void
    {
        $options['name'] = $name;
        
        $this->sendPostRequest('/profiles', [
            'data' => $options
        ]);
    }

    /**
     * Edits a profile
     *
     * @param string $name
     * @param array $options
     * @return void
     */
    public function edit(string $name, array $options = []): void
    {
        $this->sendPatchRequest("/profiles/{$name}", [
            'data' => $options
        ]);
    }
    
    /**
    *  Delete a profile
    *
    * @param string $name name
    * @return void
    */
    public function delete(string $name): void
    {
        $this->sendDeleteRequest("/profiles/{$name}");
    }
}
