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

use Exception;
use App\Form\NetworkForm;
use App\Service\Lxd\LxdCreateNetwork;
use Origin\HttpClient\Exception\ConnectionException;
use Origin\HttpClient\Exception\ClientErrorException;

/**
 * @property \App\Model\Network $Network
 */
class NetworksController extends ApplicationController
{
    protected $paginate = [
        'limit' => 20,
        'associated' => ['Host']
    ];

    public function index()
    {
        $networks = [];
        
        try {
            $networks = $this->lxd->network->list();
        } catch (ConnectionException $exception) {
            $this->Flash->error(__('Unable to connect to the host.'));
        }

        /**
         * Can't delete lxdbr0 because its used by the default profile, which I dont think we should touch.
         * Therefore the default bridge adapater is going to be removed from display for now, until a proper
         * solution can be found. The lxdbr0 is used for installation of nuber.
         */
        $networks = collection($networks)->filter(function ($network) {
            return $network['type'] === 'bridge' && $network['description'] === 'Nuber Virtual Network';
        })->toArray();

        $this->set(compact('networks'));
    }

    public function create()
    {
        $networkForm = NetworkForm::new();

        // TODO: setting IP 10.0.0.0 will trigger exception

        if ($this->request->is(['post'])) {
            $networkForm = NetworkForm::patch($networkForm, $this->request->data());
            
            $networkForm->setNetworks($this->lxd->network->list(['recursive' => 0]));

            if (empty($networkForm->ipv4_address) && empty($networkForm->ipv6_address)) {
                $this->Flash->error(__('Both IPv4 or IPv6 cannot be empty.'));
            } elseif ($networkForm->validates()) {
                $result = (new LxdCreateNetwork($this->lxd))->dispatch($networkForm->name, $networkForm->ipv4_cidr, $networkForm->ipv6_cidr);

                if ($result->success()) {
                    $this->Flash->success(__('The network was created.'));

                    return $this->redirect(['action' => 'index']);
                }
                $this->Flash->error(__('Unable to create the network.'));
            }
        }

        $this->set(compact('networkForm'));
    }

    public function edit(string $network)
    {
        $networkForm = NetworkForm::new();
      
        // check exists or trigger 404
        $info = $this->lxd->network->get($network);

        $networkForm->fromArray($info);

        if ($this->request->is(['post'])) {
            $networkForm = NetworkForm::patch($networkForm, $this->request->data());
           
            if ($networkForm->modified('name')) {
                $networkForm->setNetworks($this->lxd->network->list(['recursive' => 0]));
            }
            
            if (empty($networkForm->ipv4_address) && empty($networkForm->ipv6_address)) {
                $this->Flash->error(__('Both IPv4 or IPv6 cannot be empty.'));
            } elseif ($networkForm->validates() && $this->updateNetwork($networkForm, $info)) {
                $this->Flash->success(__('The network settings have been updated.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('Unable to update the network settings.'));
            }
        }
        $this->set('isUsed', ! empty($info['used_by']));
        $this->set(compact('networkForm', 'network'));
    }

    public function delete(string $network)
    {
        $this->request->header('Accept', 'application/json');
        $this->request->allowMethod('delete');

        // Check it exists
        $info = $this->lxd->network->get($network);
      
        // Could be used by a profile or instance or both
        if (! empty($info['used_by'])) {
            return $this->renderJson([
                'error' => [
                    'message' => __('Network is being used.'),
                    'code' => 500
                ]
            ], 500);
        }

        $this->lxd->network->delete($network);
        $this->Flash->success(__('The network was deleted.')); // display alert after redirect
      
        $this->renderJson(['data' => []]);
    }

    /**
     * @internal rename first since this can fail whilst other goes through
     *
     * @param NetworkForm $networkForm
     * @param array $info
     * @return boolean
     */
    private function updateNetwork(NetworkForm $networkForm, array $info) : bool
    {
        // Network is currently in use
        // Network "nuberbr0" already exists

        if ($info['name'] !== $networkForm->name) {
            if (empty($info['used_by'])) {
                $this->lxd->network->rename($info['name'], $networkForm->name);
            } else {
                $networkForm->error('name', __('This network is being used and cannot be renamed.'));

                return false;
            }
            $info['name'] = $networkForm->name;
        }

        $info['config']['ipv4.address'] = $networkForm->ipv4_address ? $networkForm->ipv4_address . '/' . $networkForm->ipv4_size : 'none';
        $info['config']['ipv6.address'] = $networkForm->ipv6_address ? $networkForm->ipv6_address . '/' . $networkForm->ipv6_size : 'none';

        /**
         * Assuming that this is the case, have not been able to generate other client errors
         * 10.0.0.0 - Invalid value for network "lxdbr0" option "ipv4.address": Not a usable IPv4 address "10.0.0.0/24"
         */
        try {
            $this->lxd->network->edit($networkForm->name, $info);
        } catch (ClientErrorException  $exception) {
            $message = $exception->getMessage();
            if (preg_match('/ipv4.address/', $message)) {
                $networkForm->error('ipv4_address', __('This address is not usable'));
            } elseif (preg_match('/ipv6.address/', $message)) {
                $networkForm->error('ipv6_address', __('This address is not usable'));
            }
            
            return false;
        } catch (Exception $exception) {
            return false;
        }
        
        return true;
    }
}
