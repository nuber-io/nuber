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
  <p><?= __('Add Host') ?></p>
  <!-- Button trigger modal -->

  <?php
  echo $this->Form->create($host);
  echo $this->Form->control('name', [
      'default' => __('default'),
      'after' => $this->Html->tag('small', __('A name that you can refer to this host as.'))
  ]);
  echo $this->Form->control('address', [
      'default' => explode(':', $this->request->host())[0] ?? '127.0.0.1',
      'after' => $this->Html->tag('small', __('e.g 192.168.1.10 or server1.mydomain.com'))
  ]);

  $hostConfigLink = $this->Html->link(__('Node configuration instructions'), '#', [
      'data-toggle' => 'modal',
      'data-target' => '#hostConfig'
  ]);

  echo $this->Form->control('password', [
      'type' => 'text',
      'after' => $this->Html->tag('small', __('This is the LXD host password (core.trust_password). A random UUID has been generated for your convenience.  See {link} for more information. ', ['link' => $hostConfigLink])),
      'default' => $secret
  ]);

  echo $this->Form->button(__('Add host'), ['type' => 'submit', 'class' => 'btn btn-success btn-lg']);
  echo $this->Form->end();
  ?>
</div>
<?= $this->renderShared('host-config') ?>