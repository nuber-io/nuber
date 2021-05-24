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

class NetworkForm extends Record
{
    const PATTERN_NAME = '/^[a-z][a-z0-9-]{1,14}$/i';

    protected $schema = [
        'name' => [
            'type' => 'string',
            'length' => 15
        ],
        'ipv4_range' => [
            'type' => 'string',
            'length' => 255
        ],
        'ipv4_size' => [
            'type' => 'integer',
            'length' => 3
        ],
        'ipv6_range' => [
            'type' => 'string',
            'length' => 255
        ],
        'ipv6_size' => [
            'type' => 'integer',
            'length' => 3
        ],
    ];

    protected $ipv4Addresses = [];
    protected $ipv6Addresses = [];

    protected $networks = [];

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
        $this->validate('name', $this->nameValidationRules());
        
        $this->validate('ipv4_address', [
            'optional',
            'ipv4' => [
                'rule' => ['ip', 'ipv4'],
                'message' => __('Enter a valid IPv4 address')
            ],
            'notIn' => [
                'rule' => ['notIn',$this->ipv4Addresses],
                'message' => __('This address is already in use')
            ]
        ]);

        $this->validate('ipv4_size', [
            'numeric',
            'range' => [
                'rule' => ['range',1,32],
                'message' => __('Enter a number between 1 and 32')
            ]
        ]);

        $this->validate('ipv6_address', [
            'optional',
            'ipv4' => [
                'rule' => ['ip', 'ipv6'],
                'message' => __('Enter a valid IPv6 address')
            ],
            // TODO: need is rather basic, and needs something more complicated
            'notIn' => [
                'rule' => ['notIn',$this->ipv6Addresses],
                'message' => __('This address is already in use')
            ]
        ]);

        $this->validate('ipv4_size', [
            'numeric',
            'range' => [
                'rule' => ['range',1,128],
                'message' => __('Enter a number between 1 and 128')
            ]
        ]);
    }

    private function nameValidationRules() : array
    {
        return [
            'required',
            'name' => [
                'rule' => ['regex',self::PATTERN_NAME],
                'message' => __('Letters, numbers, hypens only min 2 max 15 chars')
            ],
            'notIn' => [
                'rule' => ['notIn',$this->networks],
                'message' => __('A network with this name already exists')
            ]
        ];
    }

    public function fromArray(array $info)
    {
        // Translate
        $this->name = $info['name'];
    
        // TODO: rethink
        if (! empty($info['config']['ipv4.address'])) {
            list($this->ipv4_address, $this->ipv4_size) = $this->parseAddress($info['config']['ipv4.address']);
        }

        if (! empty($info['config']['ipv6.address'])) {
            list($this->ipv6_address, $this->ipv6_size) = $this->parseAddress($info['config']['ipv6.address']);
        }

        // set dirty to false for proper validation
        $this->reset();
    }

    private function parseAddress($value) :? array
    {
        return $value === 'none' ? [null,null] : explode('/', $value);
    }

    protected function getIpv4Cidr()
    {
        return $this->ipv4_address ? $this->ipv4_address . '/' . $this->ipv4_size : null;
    }

    protected function getIpv6Cidr()
    {
        return $this->ipv6_address ? $this->ipv6_address . '/' . $this->ipv6_size : null;
    }

    public function setNetworks(array $networks) : void
    {
        $this->networks = $networks;
        $this->validate('name', $this->nameValidationRules());
    }
}
