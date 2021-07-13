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
/**
 * @var \App\Http\View\ApplicationView $this
 */
use Origin\Core\Config;
use Origin\Security\Security;

echo $this->Html->css('form');
?>
<div class="form-header">
   <h2><?= Config::read('App.name'); ?></h2>
</div>
<div class="vertical-form">
   <p><?= __('Create user') ?></p>
   <?php
    echo $this->Form->create($user);
    echo $this->Form->control('first_name');
    echo $this->Form->control('last_name');
    echo $this->Form->control('email');
    echo $this->Form->control('password', [
        'after' => $this->Html->tag(
            'small',
            __('Randomly generated password ideas:') .
         '<br>' .
            implode('<br>', [
                Security::hex(15),
                Security::base58(15),
                Security::base62(15),
            ])
        )
    ]);
    echo $this->Form->control('password_confirm', ['type' => 'password']);
    echo $this->Form->button(__('Create User'), ['type' => 'submit', 'class' => 'btn btn-success btn-lg']);
    echo $this->Form->end();
   ?>
</div>