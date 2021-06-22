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
<?php
$statusMap = [
    'Running' => 'success',
    'Stopped' => 'secondary',
    'Frozen' => 'warning',
];
?>
<style>
    h2 {
        display: inline;
    }

    .spinner {
        display: none;
    }

    /**
     * Hack to stop line moving
     */
    .badge {
        vertical-align: top;
    }

    #listen,
    #connect {
        max-width: 100px;
    }
    .form-control {
            max-width: 500px;
        }

    main a .fas {
        color:#343a40;
    }
  
</style>
<?= $this->renderShared('validator-setup') ?>

<div class="header">
    <div class="float-right">
        <?php
        if ($meta['status'] === 'Running') {  ?>
            <button onclick="stopInstance('<?= $meta['name'] ?>')" class="btn btn-warning"><?= __('Stop') ?></button>
        <?php }

        if ($meta['status'] === 'Stopped') { ?>
            <button onclick="startInstance('<?= $meta['name'] ?>')" class="btn btn-primary"><?= __('Start') ?></button>
        <?php  } ?>
        <a href="/instances/clone/<?= $meta['name'] ?>" class="btn btn-info"><?= __('Clone') ?></a>    
        <a href="/instances" class="btn btn-secondary"><?= __('Back') ?></a>
    </div>

    <h2><?= $meta['name'] ?>&nbsp;<span class="badge badge-<?= $statusMap[$meta['status']]; ?>"><?= $meta['status']; ?></span> </h2>

    <div class="spinner">
        <div class="spinner-border text-primary " style="width: 2rem; height: 2rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <hr>
    </hr>
</div>

<ul class="list-inline">
    <li class="list-inline-item"><strong><?= __('Memory') ?></strong>: <?= $meta['meta']['memory'] ?: __('N/A') ?></li>
    <li class="list-inline-item"><strong><?= __('Disk') ?></strong>: <?= $meta['meta']['storage']?: __('N/A')  ?></li>
    <li class="list-inline-item"><strong><?= __('vCPUs') ?></strong>: <?= $meta['meta']['cpu']? $meta['meta']['cpu']  : __('N/A')  ?> </li>
    
    <?php

     $ipv4 = $ipv6 = [];

     $ips = explode(', ', $meta['meta']['ipAddress']);

     foreach ($ips as $index => $ip) {
         if (strpos($ip, ':') !== false) {
             $ipv6[] = $ip;
         } else {
             $ipv4[] = $ip;
         }
     }
    ?>
    
    <li class="list-inline-item"><strong><?= __('IPv4') ?></strong>: <?= $ipv4 ? implode(', ', $ipv4) : __('none') ?></li>
    <li class="list-inline-item"><strong><?= __('IPv6') ?></strong>: <?= $ipv6 ? implode(', ', $ipv6) : __('none') ?></li>
    <li class="list-inline-item"><strong><?= __('Type') ?></strong>: <?= $meta['type'] === 'virtual-machine' ? __('Virtual Machine') : __('Container')  ?> </li>
</ul>

<script>
    function stopInstance(instance) {
        showSpinner();
        $.get("/instances/stop/" + instance, function(data) {
            window.location.reload();
        }).always(function(){
            hideSpinner();
        }).fail(function(xhr) {
            alertError('<?= __('Error stopping instance') ?>');
            debugError(xhr);
        });
    }

    function startInstance(instance) {
        showSpinner();
        $.get("/instances/start/" + instance, function(data) {
            window.location.reload();
        }).always(function(){
            hideSpinner();
        }).fail(function(xhr) {
            var response = JSON.parse(xhr.responseText);
            alertError(response.error.message);
            debugError(xhr);
        });
    }

    function showSpinner() {
        $(".header h2 span").hide();
        $(".header .spinner").css("display", "inline");
    }

    function hideSpinner() {
        $(".header h2 span").show();
        $(".header .spinner").hide();
    }

    
    /**
    * Setup form validation and enable visual effects on submit
    */
    function initializePillForm(name){
        $("#" + name).validate({
            submitHandler: function(form) {
                $("#" + name + " .btn").attr("disabled", true);
                $("#" + name + " .btn-spinner").css("display", "inline-block");
                form.submit();
            }
        });
    }




</script>