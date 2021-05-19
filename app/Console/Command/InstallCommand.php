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
namespace App\Console\Command;

use Origin\Security\Security;
use App\Lxd\Endpoint\Certificate;
use Origin\Console\Command\Command;

class InstallCommand extends Command
{
    protected $name = 'install';
    protected $description = 'Post install command';
 
    protected function execute(): void
    {
        $source = ROOT . '/config/.env.default';
        $destination = ROOT.  '/config/.env';

        if (file_exists($destination)) {
            $this->io->status('error', 'config/.env already exists');
            $this->abort();
        }

        $template = str_replace('{key}', Security::generateKey(), file_get_contents($source));
        $template = str_replace('{uuid1}', Security::uuid(), $template);
        $template = str_replace('{uuid2}', Security::uuid(), $template);
        
        file_put_contents($destination, $template);
        $this->io->status('ok', 'config/.env created');

        $this->generateCertificates();
    }

    private function generateCertificates()
    {
        Certificate::generate(ROOT . '/config/certs');
        $this->io->status('ok', 'LXD ceritifcate and key generated');

        Certificate::generate(ROOT . '/websocket/certs');
        $this->io->status('ok', 'Websocket server ceritifcate and key generated');
    }
}
