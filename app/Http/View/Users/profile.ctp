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
    <h2><?= __('Profile'); ?></h2>
    <hr></hr>
</div>
<div class="row">
    <div class="col col-fixed">
        <?= $this->Form->create($user); ?>
        <?php
            echo $this->Form->control('first_name');
            echo $this->Form->control('last_name');
            echo $this->Form->control('email');
            echo $this->Form->button(__('Save'), ['type' => 'submit','class' => 'btn btn-primary']);
            echo $this->Form->end();
        ?>
    </div>
</div>