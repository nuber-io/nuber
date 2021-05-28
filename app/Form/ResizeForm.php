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

use App\Lib\Bytes;
use Origin\Model\Record;

class ResizeForm extends Record
{
    protected $schema = [
        'memory' => [
            'type' => 'string',
            'length' => 10
        ],
        'disk' => [
            'type' => 'string',
            'length' => 10
        ],
        'disk_usage' => [
            'type' => 'integer'
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

        $this->validate('disk', [
            'required',
            'size' => [
                'rule' => ['regex', '/^([0-9]{1,5})(MB|GB)$/'],
                'message' => __('Invalid value, use MB or GB. e.g 1GB')
            ],
            'space' => [
                'rule' => [$this,'isEnough'],
                'message' => __('This value needs to be higher than current usage'),
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

    /**
     * TODO: I recall being a 300MB issue with ZFS, which was reserved space. So I have added this
     * as a buffer.
     *
     * @return bool
     */
    public function isEnough($size): bool
    {
        $used = $this->disk_usage + 314572800;

        return Bytes::fromString($size) > $used ;
    }
}
