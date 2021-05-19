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

class IpAddressForm extends Record
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
        $this->addField('ip4_address', [
            'type' => 'string',
            'length' => 15
        ]);

        // future ready
        $this->addField('ip6_address', [
            'type' => 'string',
            'length' => 15
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
