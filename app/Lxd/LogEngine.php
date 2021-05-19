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

use Origin\Log\Engine\FileEngine;

/**
 * Log Engine for LXD requests and responses
 */
class LogEngine extends FileEngine
{

    /**
     * Formats a log message
     *
     * @param string $level
     * @param string $message
     * @param array $context
     * @return string
     */
    protected function format(string $level, string $message, array $context): string
    {
        $data = [
            'date' => date('Y-m-d G:i:s'),
            'channel' => $this->channel(),
            'level' => strtoupper($level),
            'message' => $message
        ];
   
        return json_encode(array_merge($data, $context), JSON_UNESCAPED_SLASHES);
    }
}
