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
namespace App\Service\Lxd;

use Exception;
use Origin\Service\Result;
use App\Lxd\Endpoint\Certificate;
use App\Service\ApplicationService;

class LxdAuthorizeHost extends ApplicationService
{
    private Certificate $Certificate;

    protected function execute(string $host, string $password): ?Result
    {
        $this->Certificate = new Certificate(['host' => $host]);

        try {
            if ($this->Certificate->status() === 'untrusted') {
                $this->Certificate->add($password);
            }

            return $this->result([
                'success' => true,
                'data' => [
                    'status' => $this->Certificate->status()
                ]
            ]);
        } catch (Exception $exception) {
            return $this->result([
                'success' => false,
                'error' => [
                    'message' => $exception->getMessage(),
                    'code' => $exception->getCode()
                ]
            ]);
        }
    }
}
