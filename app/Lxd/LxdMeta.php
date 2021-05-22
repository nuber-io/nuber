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
 * Extracts the memory, storage, CPU and IP address from the meta and adds this to information
 */
class LxdMeta
{
    /**
     * Adds meta information to information returned from LXD for easy access
     *
     * @param array $info
     * @return array
     */
    public static function add(array $info): array
    {
        $meta = new LxdMeta();

        return isset($info['config']) ? $meta->addMeta($info) : $meta->addMetas($info);
    }

    /**
     * Adds meta data to multiple instances
     *
     * @param array $infos
     * @return array
     */
    public function addMetas(array $infos): array
    {
        foreach ($infos as &$info) {
            $info = $this->addMeta($info);
        }

        return $infos;
    }

    /**
     * Adds the meta data to an instance
     *
     * @param array $info
     * @return array
     */
    public function addMeta(array $info): array
    {
        $info['meta'] = [
            'memory' => $info['config']['limits.memory'] ?? null,
            'storage' => $info['devices']['root']['size'] ?? null,
            'cpu' => $info['config']['limits.cpu'] ?? null,
            'ipAddress' => $this->getIpAddress($info)
        ];

        return $info;
    }

    /**
     * Finds the IP address, if it is running. Does not check the set IP address
     * anymore since this is unreliable, it can be configured for one IP address
     * but be allocated another which causes confusion
     *
     * TODO: get IPv6 address, there are two IPv6 addresses on my test setup.
     *
     * @param array $info
     * @return string
     */
    private function getIPAddress(array $info): string
    {
        $out = [];

        if ($info['status'] === 'Running' && ! empty($info['state']['network'])) {
            foreach ($info['state']['network'] as $name => $settings) {
                if ($name === 'lo') {
                    continue;
                }
             
                foreach ($settings['addresses'] as $address) {
                    if (empty($address['address']) || $address['scope'] !== 'global') {
                        continue;
                    }
                    if (in_array($address['family'], ['inet','inet6'])) {
                        $out[] = $address['address'];
                    }
                }
            }
        }

        return $out ? implode(', ', $out) : 'none';
    }
}
