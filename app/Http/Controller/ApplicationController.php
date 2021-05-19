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
namespace App\Http\Controller;

use App\Lxd\Lxd;
use Origin\I18n\I18n;

use App\Lxd\LxdClient;
use Origin\Http\Response;
use Origin\Http\Controller\Controller;

/**
 * @property \Origin\Http\Controller\Component\FlashComponent $Flash
 * @property \Origin\Http\Controller\Component\SessionComponent $Session
 * @property \Origin\Http\Controller\Component\CookieComponent $Cookie
 * @property \Origin\Http\Controller\Component\AuthComponent $Auth
 */
class ApplicationController extends Controller
{
    protected LxdClient $lxd;
    
    /**
     * Construct hook
     */
    protected function initialize(): void
    {
        $this->loadComponent('Auth', [
            'loginAction' => '/login',
            'loginRedirect' => '/instances',
            'logoutRedirect' => '/login'
        ]);
        
        $this->loadModel('Host');
    
        /**
         * Configure your locale settings here. OriginPHP ships with en_US and en_GB locales
         * by default. For others you can run the following command and it will create the locale
         * settings in config/locale.
         * $ bin/console locale:generate zh-CN ru-RU fr-FR es-ES de-DE it-IT ja-JP
         */
        I18n::initialize(['locale' => 'en_US','language' => 'en','timezone' => 'UTC']);
    }

    protected function startup(): void
    {
        $this->loadHelper('Bundle');
        
        if ($this->Auth->isLoggedIn()) {
            $this->setupLXD();
        }
    }

    /**
     * Loads the LXD hosts, sets the default host and creates the LXD client
     *
     * @return void
     */
    private function setupLXD(): void
    {
        Lxd::hosts($this->Host->find('list', [
            'fields' => ['address','name'],
            'order' => 'name ASC'
        ]));

        $host = $this->Session->exists('Lxd.host') ? $this->Session->read('Lxd.host') : array_key_first(Lxd::hosts());

        Lxd::host($host);
        $this->Session->write('Lxd.host', $host);
        $this->lxd = new LxdClient($host);
    }

    /**
     * Logs and renders JSON for errors from LXD API
     *
     * @param array $response
     * @return array
     */
    protected function transformError(array $response): array
    {
        return [
            'error' => [
                'message' => $response['err'],
                'code' => $response['status_code']
            ]
        ];
    }

    /**
     * @param array $response
     * @return \Origin\Http\Response
     */
    protected function renderJsonFromError(array $response): Response
    {
        $error = $this->transformError($response);

        return $this->renderJson($error, $error['error']['code']);
    }
}
