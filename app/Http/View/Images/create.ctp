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
    .spinner {
        display: none;
        vertical-align: middle;
    }
</style>
<div class="header">
    <h2><?= __('Create from Instance') ?></h2>
    <hr>
    </hr>
</div>
<p><?= __('Create an image using an existing instance.') ?></p>

<?= $this->renderShared('validator-setup') ?>

<div class="image form">
    <?= $this->Form->create($imageForm, [
        'id' => 'createForm'
    ]) ?>
    <?php
         echo $this->Form->control('name', [
             'label' => false,
             'placeholder' => 'e.g. apache-php , mysql',
             'after' => '<small class="form-text text-muted"> ' . __('Lowercase alphanumeric characters and dashes only, with a length between 2-62 characters.') .  '</small>',
             'regex' => '^[a-z][a-z0-9-]{1,61}$',
             'required' => true
         ]);
        echo $this->Form->control('instance', [
            'options' => empty($instances) ?  [__('No Instances')] : $instances,
            'after' => '<small class="form-text text-muted"> ' . __('Images can only be created when an instance is stopped, if your instance is not showing then stop it first.') .  '</small>',
            'disabled' => empty($instances) ? true : false
        ]);
        echo $this->Form->button(__('Create'), [
            'type' => 'submit','class' => 'btn btn-primary mr-2',
            'disabled' => empty($instances) ? true : false
        ]);
     
        echo $this->Html->link(__('Cancel'), '/images', ['class' => 'btn btn-secondary']);
        echo $this->renderShared('spinner', ['class' => 'btn-spinner']);
        echo $this->Form->end();
    ?>
</div>


<script> 
 $(document).ready(function() {
    $("#createForm").validate({
        submitHandler: function(form) {
            $("#createForm .btn").prop('disabled', true);
            $(".btn-spinner").css("display", "inline-block");
            form.submit();
        }
    });
});

</script>