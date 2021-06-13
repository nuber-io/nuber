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
namespace App\Http\View;

use Origin\Http\View\View;

/**
 * @property \Origin\Http\View\Helper\SessionHelper $Session
 * @property \Origin\Http\View\Helper\CookieHelper $Cookie
 * @property \Origin\Http\View\Helper\HtmlHelper $Html
 * @property \Origin\Http\View\Helper\FormHelper $Form
 * @property \Origin\Http\View\Helper\DateHelper $Date
 * @property \Origin\Http\View\Helper\NumberHelper $Number
 * @property \Origin\Http\View\Helper\PaginatorHelper $Paginator
 */
class ApplicationView extends View
{
    protected function initialize(): void
    {
        $this->loadHelper('LxdInstance');
        $this->loadHelper('Instance');
    }
}
