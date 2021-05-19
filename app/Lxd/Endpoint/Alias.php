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
 * @see https://lxd.readthedocs.io/en/latest/rest-api/#10imagesaliases
 *
 * $ lxc image alias --help
 * [x] create      Create aliases for existing images
 * [x] delete      Delete image aliases
 * [x] list        List image aliases
 * [X] rename      Rename aliases
 */
class Alias extends Endpoint
{
    /**
     * Gets a list of aliases
     *
     * @param array $options
     *  - recursive: levels of recursion
     * @return array
     */
    public function list(array $options = []): array
    {
        $options += ['recursive' => 1];
        $response = $this->sendGetRequest('/images/aliases', [
            'query' => [
                'recursion' => $options['recursive']
            ]
        ]);

        return $this->removeEndpoints(
            $response,
            '/1.0/images/aliases/'
        );
    }

    /**
      * Gets information on the alias
      *
      * @param string $name
      * @return array
      */
    public function get(string $name): array
    {
        return $this->sendGetRequest("/images/aliases/{$name}");
    }
 
    /**
     * Creates an alias
     *
     * @return void
     */
    public function create(string $name, string $fingerprint, string $description = null): void
    {
        $this->sendPostRequest('/images/aliases', [
            'data' => [
                'name' => $name,
                'target' => $fingerprint,
                'description' => $description
            ]
        ]);
    }

    /**
     * Renames an alias
     *
     * @param string $name
     * @param string $newName
     * @return void
     */
    public function rename(string $name, string $newName): void
    {
        $this->sendPostRequest("/images/aliases/{$name}", [
            'data' => [
                'name' => $newName,
            ]
        ]);
    }

    /**
     * Edits the description or target of anlias
     *
     * @param string $name
     * @param array $options
     * @return void
     */
    public function edit(string $name, array $options = []): void
    {
        $options += ['description' => null, 'target' => null];
        $fields = [];

        if ($options['target']) {
            $fields['target'] = $options['target']; // SHA-256
        }

        if ($options['description']) {
            $fields['description'] = $options['description'];
        }
        $this->sendPatchRequest("/images/aliases/{$name}", [
            'data' => $fields
        ]);
    }
    
    /**
    *  Delete an alias
    *
    * @param string $name fingerprint
    * @return void
    */
    public function delete(string $name): void
    {
        $this->sendDeleteRequest("/images/aliases/{$name}");
    }
}
