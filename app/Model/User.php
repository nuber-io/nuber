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
namespace App\Model;

use ArrayObject;
use Origin\Model\Entity;
use Origin\Security\Security;
use Origin\Model\Concern\Delocalizable;
use Origin\Model\Concern\Timestampable;

class User extends ApplicationModel
{
    use Delocalizable,Timestampable;

    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->validate('first_name', [
            'required'
        ]);

        $this->validate('last_name', [
            'required'
        ]);

        $this->validate('email', [
            'required',
            [
                'rule' => 'customEmail',
                'message' => __('Invalid email address')
            ],
            [
                'rule' => 'isUnique',
                'message' => __('Email address already in use')
            ]
        ]);

        $this->validate('password', [
            'required',
            [
                'rule' => 'confirm',
                'message' => __('Passwords do not match'),
                'on' => 'create'
            ],
            [
                'rule' => ['minLength', 6],
                'message' => __('Minimum length 6 characters')
            ]
        ]);
    
        // Register callbacks
        $this->beforeCreate('generateToken');
        $this->beforeSave('hashPassword');
    }

    /**
     * Callback
     *
     * @param \Origin\Model\Entity $entity
     * @param ArrayObject $options
     * @return bool
     */
    protected function generateToken(Entity $entity, ArrayObject $options): bool
    {
        $entity->token = Security::uuid();

        return true;
    }

    /**
     * Callback
     *
     * @param \Origin\Model\Entity $entity
     * @param ArrayObject $options
     * @return bool
     */
    protected function hashPassword(Entity $entity, ArrayObject $options): bool
    {
        if (! empty($entity->password) && in_array('password', $entity->modified())) {
            $entity->password = Security::hashPassword($entity->password);
        }

        return true;
    }

    /**
     * Custom email validation rule, checks that it is valid format and that domains' DNS
     * is configured for email (MX records)
     *
     * @param string $email
     * @return bool
     */
    public function customEmail(string $email): bool
    {
        $mxhosts = null;
        if (filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
            list($account, $domain) = explode('@', $email);
            getmxrr($domain, $mxhosts, $weight);
        }

        return ! empty($mxhosts);
    }

    public function passwordMatch($password, $hash): bool
    {
        return Security::verifyPassword($password, $hash);
    }
}
