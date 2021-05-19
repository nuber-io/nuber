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

class SnapshotForm extends Record
{
    const PATTERN_NAME = '/^[a-z][a-z0-9-]{1,61}$/i';

    protected $schema = [
        'name' => [
            'type' => 'string',
            'length' => 62
        ],
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
    }

    /**
     * Adjusts the validation rules
     *
     * @param array $snapshots
     * @return void
     */
    public function addExisting(array $snapshots): void
    {
        $this->validate('name', [
            'required',
            'name' => [
                'rule' => ['regex',self::PATTERN_NAME],
                'message' => __('Letters, numbers, hypens only min 2 max 62 chars')
            ],
            'in' => [
                'rule' => ['notIn', $snapshots],
                'message' => __('A snapshot already exists with this name')
            ]
        ]);
    }
}
