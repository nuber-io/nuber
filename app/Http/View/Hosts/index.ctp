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
    <div class="float-right">
        <a href="/hosts/add" class="btn btn-primary"><?= __('Add Host') ?></a>
    </div>
    <h2><?= __('Hosts') ?></h2>
    <hr></hr>
</div>
<p><?= __('Hosts are other LXD servers that nuber can manage.') ?> </p>
<div class="hosts index">
    <table class="table">
    <table class="table table-borderless">
        <thead>
            <tr>
                <th scope="col"><?= __('Name') ?></th>
                <th class="th-fixed" scope="col"><?= __('Address') ?></th>
                <th class="th-fixed" scope="col"><?= __('Created') ?></th>
                <th class="th-actions" scope="col">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($hosts as $host): ?>
            <tr>          
                <td><?= h($host->name) ?></td>
                <td><?= h($host->address) ?></td>
                <td><?= $this->Date->timeAgoInWords($host->created) ?></td>
                <td class="actions">
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?= __('Actions') ?>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <?php
                                echo $this->Html->link(__('Rename'), ['action' => 'edit', $host->id], [
                                    'class' => 'dropdown-item'
                                ]) ;
                                echo $this->Form->postLink(__('Delete'), ['action' => 'delete', $host->id], [
                                    'class' => 'dropdown-item',
                                    'confirm' => __('Are you sure you want to delete # {id}?', ['id' => $host->id])
                                ])
                            ?>
                        </div>
                    </div>
                </td><!-- actions -->
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>