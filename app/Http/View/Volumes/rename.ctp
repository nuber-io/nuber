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
<div class="header">
    <div class="float-right">
        <a href="/volumes" class="btn btn-secondary"><?= __('Back') ?></a>
    </div>
    <h2><?= __('Rename Volume') ?></h2>
    <hr></hr>
</div>
<div class="volume form">
    <?php
        echo $this->Form->create($volumeForm, [
            'id' => 'volumeForm'
        ]);
    
        echo $this->Form->control('name', [
            'default' => $name,
            'placeholder' => __('e.g. mysql-data'),
            'after' => '<small class="form-text text-muted"> ' . __('Alphanumeric characters and dashes only, with a length between 2-62 characters.') .  '</small>',
            'regex' => '^[a-z][a-z0-9-]{1,61}$',
            'required' => true
        ]);
        echo $this->Form->button(__('Rename Volume'), ['type' => 'submit', 'class' => 'btn btn-primary mr-2']);
        echo $this->Html->link(__('Cancel'), '/volumes', ['class' => 'btn btn-secondary']);
        echo $this->renderShared('spinner', ['class' => 'btn-spinner']);
        echo $this->Form->end();
    ?>
</div>

<?= $this->renderShared('validator-setup') ?>
<script> 
$(document).ready(function() {
    $("#volumeForm").validate({
        submitHandler: function(form) {
            $("#volumeForm .btn").prop('disabled', true);
            $(".btn-spinner").css("display", "inline-block");
            form.submit();   
        }
    });
});
</script>