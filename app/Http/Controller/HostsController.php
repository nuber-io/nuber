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
use Origin\Http\Response;
use Origin\Security\Security;
use Origin\Http\Exception\NotFoundException;

/**
 * @property \App\Model\Host $Host
 */
class HostsController extends ApplicationController
{
    protected $paginate = [
        'limit' => 20,
        'associated' => []
    ];

    /**
     * Switchs the currently selected host
     *
     * @return \Origin\Http\Response
     */
    public function switch() : Response
    {
        $host = $this->request->query('host');
        $hosts = Lxd::hosts();
        if (! isset($hosts[$host])) {
            throw new NotFoundException('Not Found');
        }
        $this->Session->write('Lxd.host', $host);

        return $this->redirect([
            'controller' => 'Instances',
            'action' => 'index'
        ]);
    }

    public function index()
    {
        $this->set('hosts', $this->Host->find('all', [
            'order' => 'name ASC'
        ]));
    }

    public function add()
    {
        $host = $this->Host->new();

        if ($this->request->is(['post'])) {
            $host = $this->Host->new($this->request->data(), [
                'fields' => ['name','address','password']
            ]);
         
            if ($this->Host->save($host)) {
                $this->Flash->success(__('The host was added.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The host could not be saved.'));
        } else {
            $host->password = Security::uuid();
        }

        $this->set('secret', $host->password); //
        $this->set(compact('host'));
    }

    public function edit($id = null)
    {
        $host = $this->Host->get($id);

        if ($this->request->is(['post', 'put'])) {
            $host = $this->Host->patch($host, $this->request->data(), [
                'fields' => ['name']
            ]);
            if ($this->Host->save($host)) {
                $this->Flash->success(__('The host has been updated.'));

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(__('The host could not be saved.'));
        }
        
        $this->set(compact('host'));
    }

    public function certificate() : void
    {
        $this->response->file(ROOT . '/config/certs/certificate', [
            'download' => true,
            'name' => 'nuber.crt'
        ]);
    }

    public function delete($id = null)
    {
        $this->request->allowMethod(['post']);

        $host = $this->Host->get($id);

        if ($this->Host->find('count') > 1 && $this->Host->delete($host)) {
            $this->Flash->success(__('The host was deleted.'));
        } else {
            $this->Flash->error(__('The host could not be deleted.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
