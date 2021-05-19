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
namespace App\Http\Controller;

use function Origin\Defer\defer;

class DebugController extends ApplicationController
{
    /**
     * The logs index page
     *
     * @return void
     */
    public function logs()
    {
        $log = file_exists(LOGS . '/lxd.log') ? $this->parseLog(100) : [];

        $this->set('log', array_reverse($log));
    }

    /**
     * Handles the log download
     *
     * @return void
     */
    public function download()
    {
        $this->response->file(LOGS . '/lxd.log', [
            'name' => 'log.txt',
            'download' => true
        ]);
    }

    /**
     * Parses the log file and returns the most recent X items.
     *
     * @param integer $limit
     * @return array
     */
    private function parseLog(int $limit = 20): array
    {
        $out = [];
        $fp = fopen(LOGS . '/lxd.log', 'r');
        defer($context, 'fclose', $fp);
       
        while (! feof($fp)) {
            $line = fgets($fp);
            if ($line) {
                array_push($out, $line);
            }
           
            if (count($out) > $limit) {
                array_shift($out);
            }
        }
    
        return $out;
    }
}
