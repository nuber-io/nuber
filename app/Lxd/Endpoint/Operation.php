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
 * [X] delete      Delete a background operation (will attempt to cancel)
 * [X] list        List background operations
 * [ ] show        Show details on a background operation
 */
class Operation extends Endpoint
{
    /**
     * Gets a list of operations
     *
     * @param array $options
     *  - recursive: default:1 levels of recursion
     * @return array
     */
    public function list(array $options = []): array
    {
        $options += ['recursive' => 1];
        $response = $this->sendGetRequest('/operations', [
            'query' => [
                'recursion' => $options['recursive']
            ]
        ]);

        return $this->removeEndpoints(
            $response,
            '/1.0/operations/'
        );
    }

    /**
     * Gets information on an operation
     *
     * @param string $uuid
     * @return array
     */
    public function get(string $uuid): array
    {
        $response = $this->sendGetRequest("/operations/{$uuid}");

        return $this->removeEndpoints(
            $response,
            '/1.0/containers/' // containers
        );
    }

    /**
    * Delete a background operation (will attempt to cancel)
    *
    * @param string $uuid uuid
    * @return void
    */
    public function delete(string $uuid): void
    {
        $this->sendDeleteRequest("/operations/{$uuid}");
    }

    /**
     * Waits for an operation to be completed, the timeout does not throw and error
     * it just more of a max wait time, it will come back as 'running'.
     *
     * @param string $uuid
     * @param integer|null $timeout
     * @return array
     */
    public function wait(string $uuid, int $timeout = null): array
    {
        $response = $this->sendGetRequest("/operations/{$uuid}/wait", [
            'query' => ['timeout' => $timeout],
            'timeout' => is_null($timeout) ? 0 : $timeout
        ]);

        // standard endpoint trimming
        $response = $this->removeEndpoints(
            $response,
            '/1.0/containers/' // containers
        );

        /**
         * Went with the decision to clean up endpoints to prevent lots of parsing in the future,
         * however this has now come up. Not sure what other ones will come up in the future here
         * in the wait section, since this could be for any, and we need to be able to distinguish
         */
        $response = $this->trimLogEndpoints($response);
        
        // keep track of all errors
        if ($response['err']) {
            \Origin\Log\Log::error($response['err'], [
                'description' => $response['description'],
                'resources' => $response['resources']
            ]);
        }
        
        return $response;
    }

    /**
     * Remove the precreeding parts
     * @example
     *  /1.0/instances/instance-01/logs/exec_843fb164-2322-4826-baac-044625068978.stdout -> exec_843fb164-2322-4826-baac-044625068978.stdout
     *
     * @param array $response
     * @return array
     */
    private function trimLogEndpoints(array $response): array
    {
        if (! empty($response['metadata']['output'])) {
            foreach ($response['metadata']['output'] as  &$file) {
                $pos = strrpos($file, '/');
                $file = substr($file, $pos + 1);
            }
        }

        return $response;
    }
}
