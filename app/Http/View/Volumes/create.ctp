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
    <h2><?= __('New Volume') ?></h2>
    <hr></hr>
</div>
<div class="volume form">
    <?php
        echo $this->Form->create($volumeForm, [
            'id' => 'volumeForm'
        ]);
    
        echo $this->Form->control('name', [
            'placeholder' => __('e.g. mysql-data'),
            'after' => '<small class="form-text text-muted"> ' . __('Lowercase alphanumeric characters and dashes only, with a length between 2-62 characters.') .  '</small>',
            'regex' => '^[a-z][a-z0-9-]{1,61}$',
            'required' => true
           
        ]);

        echo $this->Form->control('size', [
            'div' => 'form-group text input-fixed',
            'label' => __('Size'),
            'after' => '<small class="form-text text-muted">'. __('Set the disk size for this volume in GB. For example  1GB') .'</small>',
            'default' => '5GB',
            'required' => true,
            'regex' => '^[0-9]{1,3}GB$',
        ]);
    
        echo $this->Form->button(__('Create Volume'), ['type' => 'submit', 'class' => 'btn btn-primary mr-2']);
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