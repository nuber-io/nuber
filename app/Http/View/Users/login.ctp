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

echo $this->Html->css('form');
?>
<div class="form-header">
   <h2><?= Config::read('App.name'); ?></h2>
</div>
<div class="vertical-form">
   <p>Login to continue</p>
   <?php
   echo $this->Form->create($loginForm);
   echo $this->Form->control('email');
   echo $this->Form->control('password');
   echo $this->Form->button(__('Login'), ['type' => 'submit', 'class' => 'btn btn-success btn-lg']);
   echo $this->Form->end();
   ?>
</div>
