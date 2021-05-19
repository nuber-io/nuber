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
use Origin\Collection\Collection;

class VolumeForm extends Record
{
    protected $schema = [
        'volume' => [
            'type' => 'string',
            'length' => 62
        ],
        'path' => [
            'type' => 'string',
            'length' => 255
        ],
        'size' => [
            'type' => 'string',
            'length' => 10
        ],
        'instance' => [
            'type' => 'string',
            'length' => 62
        ],
    ];

    const PATTERN_VOLUME = '/^[a-z][a-z0-9-]{1,61}$/i';
    const PATTERN_PATH = '/^\/([a-zA-Z0-9-_\/]){1,}$/';

    /**
     * @return void
     */
    protected function initialize(): void
    {
        $this->validate('name', [
            'required',
            'name' => [
                'rule' => ['regex',self::PATTERN_VOLUME],
                'message' => __('Letters, numbers, hypens only min 2 max 62 chars')
            ]
        ]);

        $this->validate('instance', [
            'optional',
            'name' => [
                'rule' => ['regex',self::PATTERN_VOLUME],
                'message' => __('Letters, numbers, hypens only min 2 max 62 chars')
            ]
        ]);
    }

    /**
     * Adjusts the validation rules
     *
     * @param array $volumes an array of volume information (not list)
     * @return void
     */
    public function addVolumes(array $volumes): void
    {
        $this->validate('name', [
            'required',
            'name' => [
                'rule' => ['regex',self::PATTERN_VOLUME],
                'message' => __('Letters, numbers, hypens only min 2 max 62 chars'),
                'stopOnFail' => true,
            ],
            'in' => [
                'rule' => ['in', $volumes],
                'message' => __('Unkown volume')
            ]
        ]);
    }

    /**
     * Creates a simple array list of volumes from detailed array
     *
     * @param array $volumes
     * @return array
     */
    public function extractList(array $volumes): array
    {
        $collection = (new Collection($volumes))->extract('name');

        return $collection->toList();
    }

    /**
     * Sets up the form to validate creating a volume
     *
     * @param array $volumes list of volumes
     * @return void
     */
    public function validateCreate(array $volumes): void
    {
        $this->validate('name', [
            'required',
            'name' => [
                'rule' => ['regex',self::PATTERN_VOLUME],
                'message' => __('Letters, numbers, hypens only min 2 max 62 chars'),
                'stopOnFail' => true,
            ],
            'notIn' => [
                'rule' => ['notIn', $volumes],
                'message' => __('Another volume already has this name')
            ]
        ]);

        $this->validate('size', [
            'required',
            'size' => [
                'rule' => ['regex', '/^([0-9]{1,5})(MB|GB)$/'],
                'message' => __('Invalid value, use MB or GB. e.g 1GB')
            ]
        ]);
    }

    /**
     * Sets up the form to validate creating a volume
     *
     * @param array $volumes list of volumes
     * @return void
     */
    public function validateRename(array $volumes): void
    {
        $this->validate('name', [
            'required',
            'name' => [
                'rule' => ['regex',self::PATTERN_VOLUME],
                'message' => __('Letters, numbers, hypens only min 2 max 62 chars'),
                'stopOnFail' => true,
            ],
            'notIn' => [
                'rule' => ['notIn', $volumes],
                'message' => __('Another volume already has this name')
            ]
        ]);
    }

    /**
     * Sets up validation for paths
     *
     * @param array $devices
     * @return void
     */
    public function addPaths(array $devices): void
    {
        $collection = (new Collection($devices))->filter(function ($device) {
            return $device['type'] === 'disk';
        })->extract('path');

        $this->validate('path', [
            'required',
            'path' => [
                'rule' => ['regex',self::PATTERN_PATH],
                'message' => __('Invalid path'),
                'stopOnFail' => true,
            ],
            'notIn' => [
                'rule' => ['notIn', $collection->toList()],
                'message' => __('This path is used by another volume')
            ]
        ]);
    }

    /**
     * Gets the next available device name
     *
     * @param array $devices
     * @return string
     */
    public function nextDeviceName(array $devices): string
    {
        $assigned = [];

        $counter = 0;

        foreach (array_keys($devices) as $device) {
            if (substr($device, 0, 3) === 'bsv') {
                $assigned[] = (int) substr($device, 4);
            }
        }
        
        if ($assigned) {
            $counter = max($assigned) + 1;
        }

        return "bsv{$counter}";
    }
}
