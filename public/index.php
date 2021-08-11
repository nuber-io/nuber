<?php
/**
 * OriginPHP Framework
 * Copyright 2018 - 2019 Jamiel Sharief.
 *
 * Licensed under The MIT License
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * @copyright    Copyright (c) Jamiel Sharief
 * @link         https://www.originphp.com
 * @license      https://opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Front Controller
 */

/**
 * Built-in PHP server router
 */
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (file_exists(__DIR__ . $path) && is_file(__DIR__ . $path)) {
        return false;
    }
}

/**
 * Start Application bootstrap process
 */
require dirname(__DIR__) . '/config/bootstrap.php';

use App\Http\Application;

(new Application())->dispatch()->send();
