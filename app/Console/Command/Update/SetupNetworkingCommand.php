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
declare(strict_types = 1);
namespace App\Console\Command\Update;

use App\Model\Host;
use App\Lxd\LxdClient;
use Origin\Console\Command\Command;
use App\Service\Lxd\LxdCreateNetwork;

/**
 * This is a patch for users from 0.1.0 upgrading to 0.2.0
 * Problem: in 0.2.0 we introduce networks and a default network vnet0, as this is on the same addres that we used
 * before 10.0.0.1/24, creating this will cause problems with containers to start and nuberbr0 is not visible.
 */
class SetupNetworkingCommand extends Command
{
    protected $name = 'setup-networking';
    protected $description = '';

    protected Host $Host;

    protected function initialize(): void
    {
        $this->loadModel('Host');
    }
 
    protected function execute(): void
    {
        $this->out('Network update');
        $this->out('This tags nuberbr0 and creates vnet0');
        $this->io->nl();

        foreach ($this->getHosts() as $address => $name) {
            $this->processHost($address);
            $this->io->status('ok', "{$name} ({$address})");
        }
    }

    private function processHost(string $address)
    {
        $lxd = new LxdClient($address);

        $networks = $lxd->network->list(['recursive' => 0]);

        $ipv4 = '10.0.0.1/24';
        $ipv6 = 'fd00:0000:0000:0000::1/48';

        if (in_array('nuberbr0', $networks)) {
            $network = $lxd->network->get('nuberbr0');
            $network['description'] = NUBER_VIRTUAL_NETWORK;
            $lxd->network->edit('nuberbr0', $network);
        
            // Can't have network with same details or containers wont start
            $ipv4 = '10.0.1.1/24';
            $ipv6 = 'fc00:0000:0000:0000::1/48';
        }

        if (! in_array('vnet0', $networks)) {
            $result = (new LxdCreateNetwork($lxd))->dispatch('vnet0', $ipv4, $ipv6);
            if ($result->error()) {
                $this->error($result->error('message'));
            }
        }
    }

    private function getHosts() : array
    {
        return $this->Host->find('list', [
            'fields' => ['address','name'],
            'order' => 'name ASC'
        ]);
    }
}
