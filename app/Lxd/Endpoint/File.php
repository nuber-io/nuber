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
use function Origin\Defer\defer;
use App\Lxd\Endpoint\Exception\NotFoundException;

/**
 * $ lxc file --help
 * [X] delete      Delete files in instances
 * [X] edit        Edit files in instances
 * [X] pull        Pull files from instances
 * [X] push        Push files into instances
 */
class File extends Endpoint
{
    /**
     * Gets a list of files
     *
     * @param string $instance
     * @param string $path
     * @return array
     */
    public function list(string $instance, string $path): array
    {
        $response = $this->sendGetRequest("/instances/{$instance}/files", [
            'query' => ['path' => $path]
        ]);

        if (! is_array($response)) {
            throw new NotFoundException('Invalid path or directory');
        }

        return $response;
    }

    /**
     * Pulls a file from an instance
     *
     * @param string $instance
     * @param string $file /etc/password
     * @return string
     */
    public function pull(string $instance, string $file): string
    {
        $response = $this->sendGetRequest("/instances/{$instance}/files", [
            'query' => ['path' => $file]
        ]);

        if (! is_string($response)) {
            throw new NotFoundException();
        }

        return $response;
    }

    /**
     * Puts a file to the instance
     *
     * @param string $instance
     * @param string $sourcePath
     * @param string $desinationPath
     * @param array $options the following keys are supported
     *  - uid: default:0
     *  - gid: default:0
     *  - mode: default:'0700'
     * @return void
     */
    public function put(string $instance, string $sourcePath, string $desinationPath, array $options = []): void
    {
        $options += ['uid' => 0, 'gid' => 0, 'mode' => '0700'];

        $endpoint = "/instances/{$instance}/files";

        if (! file_exists($sourcePath)) {
            throw new NotFoundException();
        }

        $fp = fopen($sourcePath, 'rb');
        defer($void, 'fclose', $fp);

        $curlOptions = $this->curlConfig;

        $curlOptions[CURLOPT_INFILE] = $fp;
        $curlOptions[CURLOPT_INFILESIZE] = filesize($sourcePath);

        $this->sendPostRequest($endpoint, [
            'query' => ['path' => $desinationPath],
            'curl' => $curlOptions,
            'headers' => [
                'Content-Type' => 'application/octet-stream',
                'X-LXD-uid' => (string) $options['uid'],
                'X-LXD-gid' => (string) $options['gid'],
                'X-LXD-mode' => (string) $options['mode'],
            ],
            'type' => null // clear this, as this set as default config
        ]);
    }
}
