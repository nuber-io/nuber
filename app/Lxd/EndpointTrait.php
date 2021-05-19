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
namespace App\Lxd;

/**
 * @property \App\Lxd\Endpoint\Alias $alias
 * @property \App\Lxd\Endpoint\Backup $backup
 * @property \App\Lxd\Endpoint\Certificate $certificate
 * @property \App\Lxd\Endpoint\Console $console
 * @property \App\Lxd\Endpoint\Device $device
 * @property \App\Lxd\Endpoint\File $file
 * @property \App\Lxd\Endpoint\Host $host
 * @property \App\Lxd\Endpoint\Image $image
 * @property \App\Lxd\Endpoint\Instance $instance
 * @property \App\Lxd\Endpoint\Log $log
 * @property \App\Lxd\Endpoint\Network $network
 * @property \App\Lxd\Endpoint\Operation $operation
 * @property \App\Lxd\Endpoint\Profile $profile
 * @property \App\Lxd\Endpoint\Snapshot $snapshot
 * @property \App\Lxd\Endpoint\Storage $storage
 * @property \App\Lxd\Endpoint\Volume $volume
 */
trait EndpointTrait
{
    protected string $hostName;

    /**
     *
     * @param string $name
     * @return \App\Lxd\Endpoint|null;
     */
    public function __get($name)
    {
        $className = 'App\Lxd\Endpoint\\' . ucfirst($name);
        if (class_exists($className)) {
            return $this->$name = new $className(['host' => $this->hostName]);
        }
        trigger_error('Undefined property: ' . $name);

        return null;
    }

    /**
     * Gets the configured hostname for this endpoint
     *
     * @return string
     */
    public function hostName(): string
    {
        return $this->hostName;
    }
}
