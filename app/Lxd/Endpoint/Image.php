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
use Origin\Filesystem\Folder;
use Origin\Security\Security;
use function Origin\Defer\defer;
use PHPUnit\Framework\MockObject\RuntimeException;

/**
 * Naming should be same as lxc commands e.g info for getting
 *
 * [ ] alias       Manage image aliases
 * [ ] copy        Copy images between servers
 * [X] delete      Delete images
 * [ ] edit        Edit image properties
 * [X] export      Export and download images
 * [X] import      Import images into the image store
 * [ ] info        Show useful information about images
 * [X] list        List images
 * [ ] refresh     Refresh images
 * [ ] show        Show image properties
 *
 * @link https://lxd.readthedocs.io/en/latest/rest-api/#10images
 */
class Image extends Endpoint
{
    /**
     * Gets a list of images

    * @param array $options
     *  - recursive: recursion level e.g. 0 or 2
     *  - filter: filter name eq "my container" , config.image.os eq ubuntu
     * @return array
     */
    public function list(array $options = []): array
    {
        $options += ['recursive' => 1, 'filter' => null];

        $response = $this->sendGetRequest('/images', [
            'query' => [
                'recursion' => $options['recursive'],
                'filter' => null
            ]
        ]);

        return $this->removeEndpoints(
            $response,
            '/1.0/images/'
        );
    }

    /**
     * Gets information on an image
     *
     * @param string $name
     * @return array
     */
    public function get(string $name): array
    {
        return $this->sendGetRequest("/images/{$name}");
    }

    /**
     * Gets the image info using the alias name
     *
     * @param string $name
     * @return array
     */
    public function alias(string $name) : array
    {
        return $this->sendGetRequest("/images/aliases/{$name}");
    }

    /**
    * Deletes an image
    *
    * @param string $name fingerprint
    * @return string
    */
    public function delete(string $name): string
    {
        $result = $this->sendDeleteRequest("/images/{$name}");

        return $result['id'];
    }

    /**
     * Fetches an image from a remote and adds to the local image store
     *
     * @param string $fingerprint
     * @param array $options
     * @return string $uuid
     */
    public function fetch(string $fingerprint, array $options = []): string
    {
        $options += [
            'alias' => null,
            'mode' => 'pull',
            'public' => false,
            'autoUpdate' => false,
            'remote' => 'https://images.linuxcontainers.org:8443',
            'protcol' => 'simplestreams'
        ];

        $requestOptions = [
            'public' => $options['public'],
            'auto_update' => $options['autoUpdate'],
            'aliases' => [],
            'source' => [
                'type' => 'image',
                'mode' => $options['mode'],
                'server' => $options['remote'],
                'protocol' => $options['protcol'],
                'certificate' => null,
                'fingerprint' => $fingerprint
            ]
        ];

        if ($options['alias']) {
            $requestOptions['aliases'][] = ['name' => $options['alias'], 'description' => null];
        }

        $response = $this->sendPostRequest('/images', [
            'data' => $requestOptions
        ]);

        return $response['id'];
    }

    /**
     * Imports an image from a url
     *
     * @param string $url
     * @param array $options The following option keys
     *  - alias : adds an alias to image
     *  - public: wether this image can be downloaded by untrusted users
     * @return string
     */
    public function import(string $url, array $options = []): string
    {
        $options += ['alias' => null, 'public' => false];

        $requestOptions = [
            'public' => $options['public'],
            'aliases' => [],
            'source' => [
                'type' => 'url',
                'url' => $url
            ]
        ];

        if ($options['alias']) {
            $requestOptions['aliases'][] = ['name' => $options['alias'], 'description' => null];
        }

        $response = $this->sendPostRequest('/images', [
            'data' => $requestOptions
        ]);

        return $response['id'];
    }

    /**
     * Exports an image
     *
     * TODO: If this is going to be used need to check for multi-file and process
     *
     * @param string $fingerprint
     * @return string $file the file with full path
     */
    public function export(string $fingerprint): string
    {
        $folder = sys_get_temp_dir() . '/lxd-images';
        if (! Folder::exists($folder) && ! Folder::create($folder, ['recursive' => true])) {
            throw new RuntimeException('Error creating ' . $folder);
        }

        $file = $folder . '/' . Security::uuid(['macAddress' => true]) . '.tar.gz';

        $fp = fopen($file, 'w');
        defer($void, 'fclose', $fp);

        $curlConfig = $this->curlConfig;
        $curlConfig[CURLOPT_HEADER] = false;
        $curlConfig[CURLOPT_FILE] = $fp;

        $this->sendGetRequest("/images/{$fingerprint}/export", [
            'curl' => $curlConfig
        ]);

        return $file;
    }
}
