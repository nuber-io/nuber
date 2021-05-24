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
<div class="networks view">
    <div class="page-header">
        <div class="float-right">
            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $network->id], ['class' => 'btn btn-primary']); ?>
            <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $network->id], ['class' => 'btn btn-danger','confirm' => __('Are you sure you want to delete # {id}?', ['id' => $network->id])]); ?>
            <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-secondary']); ?>
        </div>
        <h2><?= __('Network') ?></h2>
    </div>
    <dl class="row">
            <dt class="col-sm-3"><?= __('Id') ?></dt>
        <dd class="col-sm-9"><?= h($network->id) ?></dd>
            <dt class="col-sm-3"><?= __('Host Id') ?></dt>
        <dd class="col-sm-9"><?= h($network->host_id) ?></dd>
            <dt class="col-sm-3"><?= __('Name') ?></dt>
        <dd class="col-sm-9"><?= h($network->name) ?></dd>
            <dt class="col-sm-3"><?= __('Network') ?></dt>
        <dd class="col-sm-9"><?= h($network->network) ?></dd>
            <dt class="col-sm-3"><?= __('Profile') ?></dt>
        <dd class="col-sm-9"><?= h($network->profile) ?></dd>
            <dt class="col-sm-3"><?= __('Ipv4 Range') ?></dt>
        <dd class="col-sm-9"><?= h($network->ipv4_range) ?></dd>
            <dt class="col-sm-3"><?= __('Ipv4 Size') ?></dt>
        <dd class="col-sm-9"><?= h($network->ipv4_size) ?></dd>
            <dt class="col-sm-3"><?= __('Ipv6 Range') ?></dt>
        <dd class="col-sm-9"><?= h($network->ipv6_range) ?></dd>
            <dt class="col-sm-3"><?= __('Ipv6 Size') ?></dt>
        <dd class="col-sm-9"><?= h($network->ipv6_size) ?></dd>
            <dt class="col-sm-3"><?= __('Created') ?></dt>
        <dd class="col-sm-9"><?= h($network->created) ?></dd>
            <dt class="col-sm-3"><?= __('Modified') ?></dt>
        <dd class="col-sm-9"><?= h($network->modified) ?></dd>
    
    </dl>  
   
</div>
