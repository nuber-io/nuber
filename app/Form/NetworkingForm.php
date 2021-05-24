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

class NetworkingForm extends Record
{
    const NETWORK_PATTERN = '/^[a-z][a-z0-9-]{1,14}$/i';

    protected $profiles = [];
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
        $this->addField('network', [
            'type' => 'string',
            'length' => 15
        ]);
        
        $this->addField('ip4_address', [
            'type' => 'string',
            'length' => 15
        ]);

        // future ready
        $this->addField('ip6_address', [
            'type' => 'string',
            'length' => 15
        ]);

        // these are actual profiles for networks
        $this->validate('eth0', [
            'required',
            'name' => [
                'rule' => ['regex',self::NETWORK_PATTERN],
                'message' => __('Letters, numbers, hypens only min 2 max 15 chars')
            ]
        ]);

        $this->validate('eth1', [
            'optional',
            'name' => [
                'rule' => ['regex',self::NETWORK_PATTERN],
                'message' => __('Letters, numbers, hypens only min 2 max 15 chars')
            ]
        ]);

        $this->validate('ip4_address', [
            'optional',
            'ip' => [
                'rule' => ['ip', 'ipv4'],
                'message' => __('Invalid IP Address')
            ]
        ]);

        $this->validate('ip6_address', [
            'optional',
            'ip' => [
                'rule' => ['ip', 'ipv6'],
                'message' => __('Invalid IP Address')
            ]
        ]);
    }
}
