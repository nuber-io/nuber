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

namespace App\Console\Command;

use Origin\Console\Command\Command;

class NuberResetPasswordCommand extends Command
{
    protected $name = 'nuber:reset-password';
    protected $description = 'Resets the password for Nuber control panel';

    /**
     * @var \App\Model\User
     */
    protected $User;

    protected function initialize(): void
    {
        $this->loadModel('User');
    }
 
    protected function execute(): void
    {
        $this->out('Nuber - Reset Password');

        $email = $this->io->ask('What is the user email address?');
       
        $user = $this->User->find('first', [
            'conditions' => [
                'email' => $email,
            ]
        ]);

        if ($user) {
            $user->password = $this->io->ask('What password would you like to change to');
         
            $this->User->saveOrFail($user, ['validate' => false]);
            $this->io->info('Password has been changed');
        } else {
            $this->io->error('User does not exist');
        }
    }
}
