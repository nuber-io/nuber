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
namespace App\Form;

use Origin\Model\Record;

class ForwardTrafficForm extends Record
{
    /**
     * Setup the schema and validation rules here
     *
     * @example
     *
     *   $this->addField('name', ['type'=>'string','length'=>255]);
     *   $this->validate('name', 'required');
     *
     * @return void
     */
    protected function initialize(): void
    {
        $this->addField('listen', [
            'type' => 'integer',
            'length' => 5
        ]);

        $this->addField('connect', [
            'type' => 'integer',
            'length' => 5
        ]);

        $this->addField('protocol', [
            'type' => 'string',
            'length' => 5
        ]);

        $validationRules = [
            'required',
            'integer' => [
                'rule' => 'integer',
                'message' => __('Invalid port number'),
                'stopOnFail' => true
            ],
            'between' => [
                'rule' => ['range',0,65535],
                'message' => __('Port out of range'),
            ],
           
        ];

        $this->validate('listen', $validationRules);
        $this->validate('connect', $validationRules);
        $this->validate('protocol', [
            'required',
            'in' => [
                'rule' => ['in',['tcp','udp']],
                'message' => __('Invalid Protocol')
            ]
        ]);
    }

    public function checkPortsInUse(array $devices)
    {
        $ports = [];
        foreach ($devices as $device) {
            if ($device['type'] === 'proxy') {
                list($protocol, $ip, $port) = explode(':', $device['listen']);
                $ports[] = $port;
            }
        }
        $this->validate('listen', [
            'required',
            'integer' => [
                'rule' => 'integer',
                'message' => __('Invalid port number'),
                'stopOnFail' => true
            ],
            'between' => [
                'rule' => ['range',0,65535],
                'message' => __('Port out of range'),
            ],
            'in-use' => [
                'rule' => ['notIn',$ports],
                'message' => __('Port is already configured')
            ]
        ]);
    }
}
