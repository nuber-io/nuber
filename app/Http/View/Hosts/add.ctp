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
?>
<div class="header">
    <h2><?= __('Add Host') ?></h2>
    <hr></hr>
</div>
<div class="host form">

    <?php

    echo $this->Form->create($host);
    echo $this->Form->control('name', [
        'placeholder' => __('e.g. host-01'),
        'after' => $this->Html->tag('small', __('A name that you can refer to this host as.'))
    ]);
    echo $this->Form->control('address', [
        'default' => '192.168.1.20',
        'after' => $this->Html->tag('small', __('e.g 192.168.1.10 or host1.mydomain.com'))
    ]);

    $hostConfigLink = $this->Html->link(__('Host configuration instructions'), '#', [
        'data-toggle' => 'modal',
        'data-target' => '#hostConfig'
    ]);

    echo $this->Form->control('password', [
        'type' => 'text',
        'after' => $this->Html->tag('small', __('This is the LXD host password (core.trust_password). A random UUID has been generated for your convenience. ' . ' ' . $hostConfigLink)),
        'default' => $secret
    ]);

    echo $this->Form->button(__('Save'), ['type' => 'submit', 'class' => 'btn btn-primary mr-2']);
    echo $this->Html->link(__('Cancel'), '/hosts', ['class' => 'btn btn-secondary']);
    echo $this->Form->end();
    ?>
</div>
<?= $this->renderShared('host-config') ?>