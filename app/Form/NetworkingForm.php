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

        // these are actual profiles for networks
        $this->validate('eth0', [
            'required',
            'name' => [
                'rule' => ['regex',self::NETWORK_PATTERN],
                'message' => __('Letters, numbers, hypens only min 2 max 15 chars')
            ],
            'exists' => [
                'rule' => [$this,'networkExists'],
                'message' => __('Unkown network')
            ]
        ]);

        $this->validate('eth1', [
            'optional',
            'name' => [
                'rule' => ['regex',self::NETWORK_PATTERN],
                'message' => __('Letters, numbers, hypens only min 2 max 15 chars')
            ],
            'exists' => [
                'rule' => [$this,'networkExists'],
                'message' => __('Unkown network')
            ]
        ]);
    }

    public function setNetworks(array $networks)
    {
        $this->networks = $networks;
    }

    /**
     * Undocumented function
     *
     * @param [type] $name
     * @return boolean
     */
    public function networkExists($name) : bool
    {
        return $name && (in_array($name, ['nuber-macvlan','nuber-bridged']) || in_array($name, $this->networks));
    }
}
