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
namespace App\Model;

use ArrayObject;
use Origin\Model\Entity;
use App\Service\Lxd\LxdSetupHost;
use Origin\Validation\Validation;
use App\Service\Lxd\LxdAuthorizeHost;

class Host extends ApplicationModel
{
    /**
    * This is called when the model is constructed.
    */
    protected function initialize(array $config): void
    {
        parent::initialize($config);

        $this->hasMany('AutomatedBackup', [
            'dependent' => true
        ]);

        $this->validate('name', [
            'required'
        ]);

        $this->validate('address', [
            'required',
            [
                'rule' => 'validHost',
                'message' => __('Invalid address'),
                'stopOnFail' => true
            ],
            [
                'rule' => 'isUnique',
                'message' => __('Already added'),
                'stopOnFail' => true
            ],
            [
                'rule' => 'isPingable',
                'message' => __('Could not connect to host on port 8443'),
                'stopOnFail' => true
            ],
            
        ]);

        $this->afterValidate('addCertificate', [
            'on' => 'create'
        ]);

        $this->afterCreate('setupHost');
    }

    /**
     * Checks that host address is valid
     *
     * @param string $address  192.168.1.10 or host1.mydomain.com
     * @return bool
     */
    public function validHost($address): bool
    {
        return Validation::ip($address) && $address !== '127.0.0.1' || Validation::fqdn($address, true);
    }

    /**
     * Bash command to check if port is open
     *
     * $ nc -zvw3 192.168.1.120 8443
     * @param string $address
     * @return boolean
     */
    public function isPingable($address): bool
    {
        $connected = false;

        if (is_string($address)) {
            $socket = @fsockopen($address, 8443, $errno, $errstr, 10);
            $connected = $socket !== false;
            if ($socket) {
                fclose($socket);
            }
        }
      
        return $connected;
    }

    /**
     * Is is called after validation has gone through
     *
     * @param Entity $host
     * @param ArrayObject $options
     * @return void
     */
    protected function addCertificate(Entity $host, ArrayObject $options): void
    {
        if ($host->errors() || empty($host->password)) {
            return;
        }
 
        $result = (new LxdAuthorizeHost())->dispatch($host->address, $host->password);
            
        if ($result->success) {
            return ;
        }
   
        if ($result->error['code'] === 504) {
            $host->error('address', __('Error communicating with server')); // could also be a cert problem
        } elseif ($result->error['code'] === 403) {
            $host->error('password', __('Invalid password'));
        } else {
            $host->error('address', __('Error {code}', $result->error));
        }
    }

    /**
     * Create the default profiles and private network
     *
     * @param Entity $host
     * @param ArrayObject $options
     * @return void
     */
    protected function setupHost(Entity $host, ArrayObject $options): void
    {
        (new LxdSetupHost())->dispatch($host->address);
    }
}
