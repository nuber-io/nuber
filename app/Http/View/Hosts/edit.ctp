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
    <h2><?= __('Edit Host') ?></h2>
    <hr></hr>
</div>
<div class="host form">
    <?= $this->Form->create($host) ?>
    <?php
        echo $this->Form->control('name');

        echo $this->Form->control('is_default', ['type' => 'checkbox','label' => __('Default')]);
        echo $this->Form->button(__('Save'), ['type' => 'submit','class' => 'btn btn-primary mr-2']);
        echo $this->Html->link(__('Cancel'), '/hosts', ['class' => 'btn btn-secondary']);
        echo $this->Form->end();
    ?>
</div>