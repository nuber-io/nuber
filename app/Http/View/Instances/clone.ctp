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

<?= $this->renderShared('validator-setup') ?>

<div class="header">
    <h2><?= __('Clone {instance}', ['instance' => $name]) ?></h2>
    <hr></hr>
</div>

<div class="configuration form">
    <?php
        echo $this->Form->create($cloneForm, [
            'id' => 'cloneForm'
        ]);
    
        echo $this->Form->control('name', [
            'label' => __('Instance Name'),
            'after' => '<small class="form-text text-muted">'. __('Give your instance a name, remember that you can use alphanumeric characters and dashes, and can have a length between 2-64 characters.') .'</small>',
            'regex' => '^[a-z][a-z0-9-]{1,61}$',
            'required' => true
        ]);
        echo $this->Html->tag('p', __('The hostname, the hardware address and all static IP addresses will be removed from the new clone.'));
        echo $this->Form->button(__('Clone Instance'), ['type' => 'submit', 'class' => 'btn btn-primary mr-2']);
        echo $this->Html->link(__('Cancel'), '/instances/details/' . $name, ['class' => 'btn btn-secondary']);
        echo $this->renderShared('spinner', ['class' => 'btn-spinner']);
        echo $this->Form->end();
    ?>
</div>

<script> 
$(document).ready(function() {
    $("#cloneForm").validate({
        submitHandler: function(form) {
            $("#cloneForm .btn").prop('disabled', true);
            $(".btn-spinner").css("display", "inline-block");
            form.submit();   
        }
    });
});
</script>