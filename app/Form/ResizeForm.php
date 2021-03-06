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

class ResizeForm extends Record
{
    protected $schema = [
        'memory' => [
            'type' => 'string',
            'length' => 10
        ],

        'cpu' => [
            'type' => 'string',
            'length' => 10
        ]
    ];

    protected function initialize(): void
    {
        $this->validate('memory', [
            'required',
            'size' => [
                'rule' => ['regex', '/^([0-9]{1,5})(MB|GB)$/'],
                'message' => __('Invalid value, use MB or GB. e.g 1GB')
            ]
        ]);

        $this->validate('cpu', [
            'required',
            'integer' => [
                'rule' => 'integer',
                'stopOnFail' => true
            ],
            'between' => [
                'rule' => ['range', 1,32], // TODO: this could be higher
                'message' => __('Invalid value, 1-32 CPUs')
            ]
        ]);
    }
}
