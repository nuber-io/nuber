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
namespace App\Lxd\Endpoint;

use App\Lxd\Endpoint;
use Origin\Http\Exception\BadRequestException;
use App\Lxd\Endpoint\Exception\NotFoundException;

/**
 * $ lxc config device
 *
 * [X] add         Add devices to containers or profiles
 * [X] get         Get values for container device configuration keys
 * [X] list        List container devices
 * [X] override    Copy profile inherited devices and override configuration keys
 * [X] remove      Remove container devices
 * [X] set         Set container device configuration keys
 * [ ] show        Show full device configuration for containers or profiles
 * [ ] unset       Unset container device configuration keys
 */
class Device extends Endpoint
{
    /**
     * Gets a list of devices for an instance
     *
     * @param string $name
     * @param array $options
     * @return array
     */
    public function list(string $name, array $options = []): array
    {
        $options += ['recursive' => 1];
        $info = $this->instance->info($name);

        return $options['recursive'] === 0 ? array_keys($info['devices']) : $info['devices'];
    }

    /**
     * Gets a device from an instance
     *
     * @param string $instance
     * @param string $name device
     * @return array
     */
    public function get(string $instance, string $name): array
    {
        $info = $this->instance->info($instance);

        if (! isset($info['devices'][$name])) {
            throw new NotFoundException("Device {$name} not found");
        }

        return $info['devices'][$name];
    }

    /**
     * Set container device configuration keys
     *
     * @param string $instance
     * @param string $name device
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $instance, string $name, string $key, $value): void
    {
        $info = $this->instance->info($instance);

        if (isset($info['devices'][$name])) {
            $info['devices'][$name][$key] = $value;
        } elseif (isset($info['expanded_devices'][$name])) {
            // create copy
            $info['devices'][$name] = $info['expanded_devices'][$name];
            $info['devices'][$name][$key] = $value;
        } else {
            throw new NotFoundException("Device {$name} not found");
        }

        $info['devices'] = empty($info['devices']) ? null : $info['devices'];
        
        // json: cannot unmarshal array into Go struct field ContainerPut.devices of type map[string]map[string]string
        $this->sendPatchRequest("/instances/{$instance}", [
            'data' => $info
        ]);
    }

    /**
     * Removes a device from an instance
     *
     * @param string $instance
     * @param string $name
     * @return void
     */
    public function remove(string $instance, string $name): void
    {
        $info = $this->instance->info($instance);

        if (! isset($info['devices'][$name])) {
            throw new NotFoundException("Device {$name} not found");
        }
      
        unset($info['devices'][$name]);
        
        // json: cannot unmarshal array into Go struct field ContainerPut.devices of type map[string]map[string]string
        $info['devices'] = empty($info['devices']) ? null : $info['devices'];
        
        $this->sendPutRequest("/instances/{$instance}", [
            'data' => $info
        ]);
    }

    /**
     * Adds a device an instance (sync)
     *
     *  lxc config device add instance-dev myport80 proxy listen=tcp:0.0.0.0:80 connect=tcp:127.0.0.1:80
     *
     *  $device->add('ubuntu-01','myport80', [
     *    'connect' => 'tcp:127.0.0.1:80'
     *    'listen' => 'tcp:0.0.0.0:80'
     *    'type' => 'proxy'
     *  ]);
     *
     * @param string $instance
     * @param string $name
     * @param array $options
     * @return void
     */
    public function add(string $instance, string $name, array $options = []): void
    {
        $info = $this->instance->info($instance);

        // added error handler
        if (isset($info['devices'][$name])) {
            throw new BadRequestException("Device {$name} already exists");
        }

        $this->sendPatchRequest("/instances/{$instance}", [
            'data' => [
                'devices' => [
                    $name => $options
                ]
            ]
        ]);
    }

    /**
     * This overwrites the device settings
     *
     * @param string $instance
     * @param string $name
     * @param array $options
     * @return void
     */
    public function override(string $instance, string $name, array $options = []): void
    {
        $this->sendPatchRequest("/instances/{$instance}", [
            'data' => [
                'devices' => [
                    $name => $options
                ]
            ]
        ]);
    }
}
