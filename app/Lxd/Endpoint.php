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
namespace App\Lxd;

use Exception;
use Origin\Log\Log;
use Origin\HttpClient\Http;
use Origin\HttpClient\Response;

/**
 * @see https://lxd.readthedocs.io/en/stable-4.0/rest-api/
 *
 */
class Endpoint
{
    /**
     * Some end points require access to other endpoints, e.g. devices, or volumes. Mainly
     * instance endpoint. Using this traits makes it easy to use other endpoints from within
     * this endpoint.
     */
    use EndpointTrait;
    /**
     * HTTP client for requests
     */
    protected Http $client;

    protected array $curlConfig = [
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSLCERT => ROOT . '/config/certs/certificate',
        CURLOPT_SSLKEY => ROOT . '/config/certs/privateKey'
    ];

    public function __construct(array $options = [])
    {
        $options += ['host' => Lxd::host(),'timeout' => 10];

        if (empty($options['host'])) {
            throw new Exception(__('No LXD host is set'));
        }

        $this->hostName = $options['host'];

        // Handle IPv6 address
        if (filter_var($options['host'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
   
            /**
             * Cant test on docker mac
             * @see https://curl.se/libcurl/c/CURLOPT_IPRESOLVE.html
             */
            // $curlConfig[CURLOPT_IPRESOLVE] = CURL_VERSION_IPV6;
            $options['host'] = "[{$options['host']}]";
        }

        $this->client = new Http([
            'base' => "https://{$options['host']}:8443",
            'curl' => $this->curlConfig,
            'type' => 'json',
            'httpErrors' => false,
            'timeout' => $options['timeout']
        ]);

        $this->adjustSeverTimeout($options['timeout']);
    }

    /**
     * A longer running request such as importing a large backup is going
     * to take more than the default 30 seconds, but not all requests
     *
     * @param integer $seconds
     * @return void
     */
    private function adjustSeverTimeout(int $seconds): void
    {
        if ($seconds > (int) ini_get('max_execution_time')) {
            set_time_limit($seconds + 10);
        }
    }

    /**
     * A wrapper for passing all requests through this to enable debugging.
     *
     * @param string $method
     * @param string $path
     * @param array $options
     * @return mixed
     */
    private function sendRequest(string $method, string $path, array $options = [])
    {
        // HELP with logging
        $url = $path;
        if (! empty($options['query']) && is_array($options['query'])) {
            $url .= '?' . http_build_query($options['query']);
        }

        if (! empty($options['timeout'])) {
            $this->adjustSeverTimeout($options['timeout']);
        }

        $request = strtoupper($method) . ' ' . $url;

        $response = $this->client->$method($path, $options);
        $hasError = $response->statusCode() >= 400 && $response->statusCode() <= 599;
        $level = $hasError ? 'error' :'info';

        // Skip binary responses
        if (! in_array($response->headers('Content-Type'), ['application/octet-stream'])) {
            Log::write($level, $request, [
                'channel' => 'lxd',
                'host' => $this->hostName,
                'method' => strtoupper($method),
                'requestURL' => 'https://' . $this->hostName . ':8443' . $url,
                'requestBody' => $options['data'] ?? null,
                'responseHeaders' => $response->headers(),
                'responseCode' => $response->statusCode(),
                'responseBody' => $response->json() ?: $response->body()
            ]);
        }
     
        return $this->responseHandler($response);
    }

    /**
     * Get Request
     *
     * @param string $path /containers
     * @param array $options
     * @return mixed
     */
    protected function sendGetRequest(string $path, array $options = [])
    {
        return $this->sendRequest('get', "/1.0{$path}", $options);
    }

    /**
     * Get Request
     *
     * @param string $path /containers
     * @param array $options
     * @return mixed
     */
    protected function sendPutRequest(string $path, array $options = [])
    {
        return $this->sendRequest('put', "/1.0{$path}", $options);
    }

    /**
     * Post Request
     *
     * @param string $path /containers
     * @param array $options
     * @return mixed
     */
    protected function sendPostRequest(string $path, array $options = [])
    {
        return $this->sendRequest('post', "/1.0{$path}", $options);
    }

    /**
     * Delete Request
     *
     * @param string $path /containers
     * @param array $options
     * @return array
     */
    protected function sendDeleteRequest(string $path, array $options = []): array
    {
        return $this->sendRequest('delete', "/1.0{$path}", $options);
    }

    /**
     * Patch request
     *
     * @param string $path /containers
     * @param array $options
     * @return array
     */
    protected function sendPatchRequest(string $path, array $options = []): array
    {
        return $this->sendRequest('patch', "/1.0{$path}", $options);
    }

    /**
     * process response, if error handler that as well.
     *
     * @param \Origin\HttpClient\Response $response
     * @return array
     */
    private function responseHandler(Response $response)
    {
        if ($response->statusCode() >= 400 && $response->statusCode() <= 599) {
            (new ErrorHandler())->triggerError($response);
        }

        if ($response->headers('content-type') === 'application/json') {
            $content = $response->json();

            return json_last_error() === JSON_ERROR_NONE ? $content['metadata'] : $response->body();
        }

        return $response->body();
    }

    /**
    * Remove endpoints now to save having to parse later
    *
    * @param array $data
    * @param string $needle
    * @return array
    */
    protected function removeEndpoints(array $data, string $needle): array
    {
        $offset = strlen($needle);

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = $this->removeEndpoints($value, $needle);
            } elseif (is_string($value) && substr($value, 0, $offset) === $needle) {
                $value = substr($value, $offset);
            }
            $data[$key] = $value;
        }

        return $data;
    }
}
