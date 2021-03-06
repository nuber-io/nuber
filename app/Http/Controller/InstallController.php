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

use Origin\Text\Text;
use Origin\Security\Security;
use Origin\ValueStore\ValueStore;
use Origin\Http\Exception\NotFoundException;

/**
 * @property \App\Model\User $User
 * @property \App\Model\Host $Host
 */
class InstallController extends ApplicationController
{
    protected $layout = 'form';

    protected function initialize(): void
    {
        parent::initialize();

        $this->Auth->allow([
            'user',
            'host'
        ]);

        $this->loadModel('User');
        $this->loadModel('Host');
    }

    public function user()
    {
        $settings = new ValueStore(config_path('install.json'));

        // Backward compatability DB user count until a patch can be added
        if ($settings->user || $this->User->count() > 0) {
            throw new NotFoundException('Not Found');
        }

        $user = $this->User->new();

        if ($this->request->is(['post'])) {
            $user = $this->User->new($this->request->data());

            if ($this->User->save($user)) {

                # write to config/install.php
                $settings->user = true;
                $settings->save();

                $this->Flash->success(__('You have been added as a user'));

                return $this->redirect('/install/host');
            }

            $this->Flash->error(__('Your account could not be created.'));
        }
        $this->set('user', $user);
    }

    public function host()
    {
        $settings = new ValueStore(config_path('install.json'));

        $host = $this->Host->new();

        // Backward compatability DB host count until a patch can be added
        if ($settings->host || $this->Host->count() > 0) {
            throw new NotFoundException('Not Found');
        }
        $host->password = Security::uuid();
        if ($this->request->is(['post'])) {
            $host = $this->Host->new($this->request->data());
          
            $host->is_default = true;

            if ($this->Host->save($host)) {
                $settings->host = true;
                $settings->save();
               
                # Adjust the .env file to use the LXD host address
                /* file_put_contents(
                     CONFIG . '/.env',
                     Text::replace('https://localhost:3000', "https://{$host->address}", file_get_contents(CONFIG . '/.env'))
                 );
                 */
                $this->Flash->success(__('The host has been added.'));

                return $this->redirect('/login');
            }
            $this->Flash->error(__('The host could not be added.'));
        }
        
        $this->set('secret', $host->password);
        $this->set('host', $host);
    }
}
