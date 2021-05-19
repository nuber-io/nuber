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
namespace App\Http\View\Helper;

use DateTime;
use App\Lib\Bytes;

/**
 * TODO: rename this helper as its used not just by instances
 */
class LxdInstanceHelper extends ApplicationHelper
{
    protected $frequencies = [];

    protected function initialize() : void
    {
        $this->frequencies = [
            'hourly' => __('Hourly'),
            'daily' => __('Daily'),
            'weekly' => __('Weekly'),
            'monthly' => __('Monthly'),
        ];
    }

    public function frequency(string $frequency) : string
    {
        return $this->frequencies[$frequency] ?? 'error';
    }

    public function description(array $info): string
    {
        $out = [];
        if (! empty($info['meta']['memory'])) {
            $out[] = $info['meta']['memory'];
        }
        if (! empty($info['meta']['storage'])) {
            $out[] = $info['meta']['storage'];
        }

        if (! empty($info['meta']['cpu'])) {
            $out[] = $info['meta']['cpu'] . 'CPU';
        }

        return implode(' ', $out);
    }

    /**
     * @param string $datetime
     * @return string
     */
    public function convertISOdate(string $datetime): string
    {
        $createdAt = DateTime::createFromFormat('Y-m-d\TH:i:s+', $datetime);

        return $createdAt->format('Y-m-d H:i:s');
    }

    /**
     * This works if there are limits.memory set
     */
    public function memoryUsage(array $info)
    {
        if (empty($info['state']['memory']['usage']) || empty($info['meta']['memory'])) {
            return 0;
        }
        $out = 0;

        $bytes = Bytes::fromString($info['meta']['memory']);
        if ($bytes) {
            $out = ($info['state']['memory']['usage'] / $bytes) * 100 ;
        }
       
        return round($out, 2);
    }

    public function diskUsage(array $info)
    {
        if (empty($info['state']['disk']['root']['usage']) || empty($info['meta']['storage'])) {
            return 0;
        }
        $out = 0;
      
        $bytes = Bytes::fromString($info['meta']['storage']);
        if ($bytes) {
            $out = ($info['state']['disk']['root']['usage'] / $bytes) * 100 ;
        }

        return round($out, 2);
    }

    /**
     * Gets the backup frequency from the snapshot name
     *
     * @param string $snapshot
     * @return string
     */
    public function backupFrequency(string $snapshot) : string
    {
        $frequency = 'unkown';

        if (preg_match('/^backup-(hourly|daily|weekly|monthly)-\d{10}$/', $snapshot, $matches)) {
            $frequency = $matches[1];
        }

        return $frequency;
    }
}
