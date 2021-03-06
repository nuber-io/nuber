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
    <h2><?= __('New Instance') ?></h2>
    <hr></hr>
</div>

<style> 
     .input-fixed .form-control {
        width:150px;
    }
</style>

<div class="configuration form">

    <?php
    
    $this->Form->controlDefaults([
        'radio' => ['div' => 'form-check form-check-inline', 'class' => 'form-check-input', 'label' => ['class' => 'form-check-label']]]);

        echo $this->Form->create($instanceForm);
    
        echo $this->Form->control('name', [
            'label' => __('Instance Name'),
            'after' => '<small class="form-text text-muted">'. __('Give your instance a name, remember that you can use lowercase alphanumeric characters and dashes, and can have a length between 2-64 characters.') .'</small>',
        ]);

        echo $this->Form->control(
            'type',
            [
                'type' => 'radio',
                'default' => $this->request->query('type') ?: 'container',
                'options' => [
                    'container' => __('Container'),
                    'virtual-machine' => __('Virtual Machine')

                ],
                'disabled' => ! $supportsVMs || $this->request->query('store')
            ],
        );

        if (! $supportsVMs || $this->request->query('store')) {
            echo $this->Form->hidden('type', ['value' => $this->request->query('type') ?: 'container']);
        }

        ?>        
        <small class="form-text text-muted"><?=  __('Select whether this will be a container or virtual machine.') ?></small>  


        <legend  class="w-auto"><?= __('Resources') ?></legend>
        
         <?php
        echo $this->Form->control('memory', [
            'div' => 'form-group text input-fixed',
            'label' => __('Memory Limit'),
            'after' => '<small class="form-text text-muted">'. __('Set the memory limit that the instance can use. You can use MB or GB, for example 512MB or 1GB') .'</small>',
            'default' => '1GB'
        ]);
        
        echo $this->Form->control('disk', [
            'div' => 'form-group text input-fixed',
            'label' => __('Hard Drive'),
            'after' => '<small class="form-text text-muted">'. __('Set the disk size space for the instance. You can use MB or GB, for example 512MB or 1GB') .'</small>',
            'default' => '20GB'
        ]);

        echo $this->Form->control('cpu', [
            'div' => 'form-group text input-fixed',
            'label' => __('CPU Limit'),
            'after' => '<small class="form-text text-muted">'. __('Set the maximum amount of CPUs that this instance can use.') .'</small>',
            'default' => 1
        ]);

        ?> 
        <legend  class="w-auto"><?= __('Networking') ?></legend>
        
         <?php

        echo $this->Form->control('eth0', [
            'label' => __('eth0'),
            'options' => $networks,
            'value' => 'vnet0', // HIT or miss
     
        ]);
    
        echo $this->Form->hidden('image');

        echo $this->Form->button(__('Create Instance'), ['type' => 'submit', 'class' => 'btn btn-primary mr-2']);
        echo $this->Html->link(__('Cancel'), '/instances', ['class' => 'btn btn-secondary']);
        echo $this->Form->end();
    ?>
</div>