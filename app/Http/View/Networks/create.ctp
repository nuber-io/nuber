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

<style>
    .ipv4_address , .ipv4_size {
        display:inline;
        max-width: 50%;
    }

</style>
<div class="page-header">
    <h2><?= __('New Network') ?></h2>
    <hr></hr>
</div>

<div class="network form">
    <?= $this->Form->create($networkForm, [
        'id' => 'networkForm'
    ]); ?>
    <?php
             
        echo $this->Form->control('name', [
            'label' => __('Network name'),
            'after' => '<small class="form-text text-muted">'. __('Give the network interface a name, remember that you can use lowercase alphanumeric characters and dashes, and can have a length between 2-15 characters.') .'</small>',
            'placeholder' => 'e.g. virtual-network',
            'regex' => '^[a-z][a-z0-9-]{1,14}$',
            'required' => true
        ]);

    echo $this->Html->tag('p', __('Here you can set the IP address ranges using CIDR notation, e.g. 10.0.0.1/24.'));
    
    echo $this->Html->div(
        $this->Form->control('ipv4_address', [
            'label' => __('IPv4 address prefix'),
            'div' => 'col-8',
            'after' => '<small class="form-text text-muted"> ' . __('Set an IPv4 address which will be used for the range. e.g. 10.0.0.1, or leave blank to not use IPv4') .  '</small>',
            'regex' => '^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$'
        ]) .

        $this->Form->control('ipv4_size', [
            'label' => __('Prefix size'),
            'div' => 'col-4',
            'options' => array_combine(range(1, 32), range(1, 32)),
            'default' => 24,
        ]),
        ['class' => 'form-group form-row']
    );

    ?>
  
<?php

    echo $this->Html->div(
        $this->Form->control('ipv6_address', [
            'label' => __('IPv6 address prefix'),
            'div' => 'col-8',
            'after' => '<small class="form-text text-muted"> ' . __('Set an IPv6 address which will be used for the range. e.g. fd42:603c:9fbb:199::1, or leave blank to not use IPv6') .  '</small>',
            'regex' => '^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$'
        ]) .
        $this->Form->control('ipv6_size', [
            'label' => __('Prefix size'),
            'div' => 'col-4',
            'options' => array_combine(range(1, 128), range(1, 128)),
            'default' => 64
        ]),
        ['class' => 'form-group form-row']
    );

    echo $this->Form->button(__('Save'), ['type' => 'submit', 'class' => 'btn btn-primary mr-2']);
    echo $this->Html->link(__('Cancel'), '/networks', ['class' => 'btn btn-secondary']);
    echo $this->renderShared('spinner', ['class' => 'btn-spinner']);
    echo $this->Form->end();
    ?>
</div>

<?= $this->renderShared('validator-setup') ?>

<script> 
$(document).ready(function() {
    $("#networkForm").validate({
        submitHandler: function(form) {
            $("#networkForm .btn").prop('disabled', true);
            $(".btn-spinner").css("display", "inline-block");
            form.submit();   
        }
    });
});
</script>