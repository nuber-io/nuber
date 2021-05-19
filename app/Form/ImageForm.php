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

class ImageForm extends Record
{
    const PATTERN_NAME = '/^[a-z][a-z0-9-]{1,61}$/i';
    
    protected $schema = [
        'name' => [
            'type' => 'string',
            'length' => 62
        ],
        'instance' => [
            'type' => 'string',
            'length' => 62
        ]
    ];
    
    protected function initialize(): void
    {
        $this->validate('name', [
            'required',
            'name' => [
                'rule' => ['regex',self::PATTERN_NAME],
                'message' => __('Letters, numbers, hypens only min 2 max 62 chars')
            ]
        ]);

        $this->validate('instance', [
            'required',
            'name' => [
                'rule' => ['regex',self::PATTERN_NAME],
                'message' => __('Letters, numbers, hypens only min 2 max 62 chars')
            ]
        ]);
    }
}
