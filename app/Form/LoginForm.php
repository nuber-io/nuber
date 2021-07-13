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

class LoginForm extends Record
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
        $this->addField('email', [
            'type' => 'string',
            'length' => 255
        ]);

        $this->addField('password', [
            'type' => 'string',
            'length' => 32
        ]);

        $this->validate('email', [
            'required',
            'email',
            'min' => [
                'rule' => ['minLength', 3],
                'message' => __('Invalid email address')
            ],
            'max' => [
                'rule' => ['maxLength',255],
                'message' => __('Invalid email address')
            ]
        ]);
        
        $this->validate('password', [
            'required',
            'min' => [
                'rule' => ['minLength', 8],
                'message' => __('Invalid password')
            ],
            'max' => [
                'rule' => ['maxLength',32],
                'message' => __('Invalid password')
            ]
        ]);
    }
}
