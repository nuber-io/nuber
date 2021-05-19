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
?>
<style>
    .image form {
        max-width:500px;
    }
</style>
<div class="header">
    <div class="float-right">
        <a href="/images" class="btn btn-secondary"><?= __('Back') ?></a>
    </div>
    <h2><?= __('Download Image') ?></h2>
    <hr>
    </hr>
</div>
<p><?= __('Here you can download images from the linuxcontainers.org image server to use locally.') ?></p>


<div class="image form">
 
    <?php
        echo $this->Form->create(null);
        echo $this->Form->control('image', [
            'placeholder' => __('e.g. ubuntu/focal/amd64 or centos/8/amd64'),
            'after' => '<small class="form-text text-muted">' . __('You can search the available remote images here.') . '</small>'
        ]);

        echo $this->Form->button(__('Download'), ['type' => 'submit', 'class' => 'btn btn-primary']);
        echo $this->Form->end();
    ?>
</div>
<script>
    $(function() {
        var images = <?= json_encode($remoteImages) ?>;

        /**
         * All validation handled here, the value must not be
         * empty and exist in the list
         */
        $("#image").autocomplete({
            source: images,
            change: function(event, ui) {
                if (ui.item == null) {
                    event.currentTarget.value = '';
                    event.currentTarget.focus();
                }
            }
        });
        $("form").submit(function(event) {
            if ($("#image").val() === "") {
                event.preventDefault();
            }
        });
    });
</script>
<link rel="stylesheet" href="/css/jquery-ui.css">