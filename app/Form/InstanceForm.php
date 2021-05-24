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

class InstanceForm extends Record
{
    const PATTERN_NAME = '/^[a-z][a-z0-9-]{1,61}$/i';

    protected $schema = [
        'name' => [
            'type' => 'string',
            'length' => 62
        ],
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
        $this->validate('name', [
            'required',
            'name' => [
                'rule' => ['regex',self::PATTERN_NAME],
                'message' => __('Letters, numbers, hypens only min 2 max 62 chars')
            ]
        ]);
    }

    /**
     * Enables validation for memory, disk and cpu settings
     *
     * @return void
     */
    public function validateConfig(): void
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

        $this->validate('eth0', [
            'required',
            'name' => [
                'rule' => ['regex', NetworkForm::PATTERN_NAME],
                'message' => __('Letters, numbers, hypens only min 2 max 15 chars')
            ]
        ]);
    }

    /**
     * Adjusts the validation rules
     *
     * @param array $instances
     * @return void
     */
    public function addExisting(array $instances): void
    {
        $this->validate('name', [
            'required',
            'name' => [
                'rule' => ['regex',self::PATTERN_NAME],
                'message' => __('Letters, numbers, hypens only min 2 max 62 chars')
            ],
            'in' => [
                'rule' => ['notIn', $instances],
                'message' => __('An instance already exists with this name')
            ]
        ]);
    }

    /**
    * Gets the default name from the image name
    *
    * @param string $image
    * @return string
    */
    public function defaultName(string $image): string
    {
        // create default name
        $name = $image;
        if (strpos($image, '/') !== false) {
            list($dist, $other) = explode('/', $image, 2);
            $name = $dist;
        }

        return $name;
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
