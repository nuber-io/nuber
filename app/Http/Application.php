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
namespace App\Http;

use Origin\Http\BaseApplication;
use Origin\Http\Middleware\IdsMiddleware;
use Origin\Http\Middleware\MinifyMiddleware;
use Origin\Http\Middleware\SessionMiddleware;
use Origin\Http\Middleware\CsrfProtectionMiddleware;

class Application extends BaseApplication
{
    const CSRF_OPTIONS = [
        // this application has lots of ajax requests
        'singleUse' => false
    ];
    
    const MINIFY_OPTIONS = [
        'conservativeCollapse' => true, // Ensures there is at least one space between tags
        'minifyJs' => false,//true, // Minifies inline Javascript
        'minifyCss' => true // Minifies inline Styles
    ];

    protected function initialize(): void
    {
        $this->addMiddleware(new SessionMiddleware);
        $this->addMiddleware(new CsrfProtectionMiddleware(self::CSRF_OPTIONS));
        $this->addMiddleware(new MinifyMiddleware(self::MINIFY_OPTIONS));
        $this->addMiddleware(new IdsMiddleware);
    }
}
