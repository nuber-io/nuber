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

use Origin\HttpClient\Response;
use App\Lxd\Endpoint\Exception\NotFoundException;
use Origin\HttpClient\Exception\ClientErrorException;
use Origin\HttpClient\Exception\ServerErrorException;

class ErrorHandler
{
    /**
    * Status Codes and messages
    * @var array
    */
    private array $statusCodes = [
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        444 => 'Connection Closed Without Response',
        451 => 'Unavailable For Legal Reasons',
        499 => 'Client Closed Request',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
        599 => 'Network Connect Timeout Error'
    ];
 
    /**
     * Triggers a 4xx or 5xx Error
     *
     * @param string $code
     * @return void
     */
    public function triggerError(Response $response): void
    {
        $statusCode = $response->statusCode();
        $message = $this->statusCodes[$statusCode] ? $statusCode . ' ' . $this->statusCodes[$statusCode] : 'HTTP Error: ' .$statusCode;

        // Extract LXD error messages and codes, these can vary from HTTP code
        if ($response->headers('content-type') === 'application/json') {
            $content = $response->json();
            if (json_last_error() === JSON_ERROR_NONE) {
                $message = $content['error'] ?? $message;
                $statusCode = $content['error_code'] ?? $statusCode;
            }
        }

        // isolate 404
        if ($statusCode === 404) {
            throw new NotFoundException($message, 404);
        }

        if ($statusCode >= 400 && $statusCode <= 499) {
            throw new ClientErrorException($message, $statusCode);
        }
        throw new ServerErrorException($message, $statusCode);
    }
}
