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

use App\Lxd\Lxd;

?>
<style>
    .progress {
        width: 100px;
    }

    .fa-success {
        color: #28a745;
    }

    .fa-secondary {
        color: #6c757d;
    }

    .fa-warning {
        color: #ffc107;
    }

    .instances .table .th-fixed {
        min-width: 150px;
    }

    .instances .table .th-actions {
        width: 150px;
    }

    .spinner-border {
        display: none;
    }

    .spinner-border.display {
        display: inline-block;
    }
</style>

<div class="header">
    <div class="float-right">
        <a href="/instances/wizard" class="btn btn-primary"><?= __('New Instance') ?></a>
    </div>
    <h2>
        <?= __('Instances') ?> &nbsp;
        <small class="text-muted"><?= Lxd::hosts()[$this->Session->read('Lxd.host')] ?></small>
    </h2>
    <hr>
    </hr>
</div>
<script>

/**
 * This is here as ajax restarting requires reloading and therefore works better instead of data-attributes.
 * IMPORTANT: keep before row rendering for now.
 */
function updateDiskUsage(instance,id) {
    $.get( "/instances/disk_usage/" + instance, function( data ) {
        $('#' + id + ' .progress-disk .progress-bar').width(data.usage);
    });
}

</script>

<div class="instances index">
    <table class="table table-borderless">
        <thead>
            <tr>
                <th scope="col"><?= __('Name') ?></th>
                <th class="th-fixed" scope="col"><?= __('IP address') ?></th>
                <th class="th-fixed" scope="col"><?= __('Memory') ?></th>
                <th class="th-fixed" scope="col"><?= __('Disk') ?></th>
                <th class="th-fixed" scope="col"><?= __('Status') ?></th>
                <th class="th-fixed" scope="col"><?= __('Created') ?></th>
                <th class="th-actions" scope="col">&nbsp;</th>
            </tr>
        </thead>
        <tbody>

            <?php

            foreach ($instances as $instance) {
                echo $this->renderShared('instance-row', ['instance' => $instance]);
            }
            if (empty($instances)) {
                ?>
                <tr>
                    <td colspan="6"><?= __('No instances') ?> </td>
                </tr>
            <?php
            }

            ?>
        </tbody>
    </table>


    <?php if ($this->request->query('download')) :  ?>

        <?php
            echo $this->render('partials/load-screen', [
                'title' => __('Downloading Image'),
                'text' => __('Please wait whilst the image is downloaded.')
            ]);
        ?> 

        <script> 

            showLoadScreen();

            $.ajax({
                type: "POST",
                url: "/instances/download/<?= $this->request->query('download') ?>",
                data: {},
                success: function(data) {
                    console.log(data);
                    location.href = "/instances?create=<?= $this->request->query('instance') ?>";
                },
                error: function(xhr) {
                    hideLoadScreen();
                    alertError('<?= __('Error downloading image.') ?>');
                    debugError(xhr);
                },
                timeout: 0
            });
        </script>
        <?php endif ?>


  
  <?php if ($this->request->query('create')) :  ?>

    <?php
        echo $this->render('partials/load-screen', [
            'title' => __('Creating Instance'),
            'text' => __('Please wait whilst the instance is created.')
        ]);
    ?> 

    <script> 
        showLoadScreen();
        $.ajax({
            type: "POST",
            url: "/instances/init/<?= $this->request->query('create') ?>",
            data: {},
            success: function(data) {
                console.log(data);
                location.href = "/instances/details/<?= $this->request->query('create') ?>";
            },
            error: function(xhr) {
                hideLoadScreen();
                alertError('<?= __('Error creating instance.') ?>');
                debugError(xhr);
            },
            timeout: 0
        });
    </script>
    <?php endif ?>
    
    <script>
        function restart(instance, id) {
            instanceAction('restart', instance, id);
        }

        function start(instance, id) {
            instanceAction('start', instance, id);
        }

        function stop(instance, id) {
            instanceAction('stop', instance, id);
        }

        function remove(instance, id) {
            if (confirm('<?= __('Are you sure you want to delete this instance, data will be lost?') ?>')) {
                instanceAction('delete', instance, id);
            }
        }

        function instanceAction(action, instance, id) {

            var errors = { "start": "<?= __('Error starting the instance') ?>","stop": "<?= __('Error stopping the instance') ?>","restart": "<?= __('Error restarting the instance') ?>"  }; 

            $('#' + id + '-status .badge').hide();
            $('#' + id + '-status .spinner-border').show();
            $.get("/instances/" + action + "/" + instance, function(data) {

                if (action === 'delete') {
                    $("#" + id).remove();
                    return;
                }

                $.get("/instances/row/" + instance, function(data) {
                    $("#" + id).replaceWith(data);
                });
            }).fail(function(xhr) {
                $('#' + id + '-status .badge').show();
                $('#' + id + '-status .spinner-border').hide();
                alertError(errors[action]);
                debugError(xhr);
            });
        }
   
        $(document).ready(function() {
            // Update disk usage for each instance
          /*  $('.progress-disk[data-instance]').each(function() {
                var progressBar = $(this).find('.progress-bar');
                var instance = $(this).attr('data-instance');
                $.get( "/instances/disk_usage/" + instance, function( data ) {
                    progressBar.width(data.usage);
                });
            });*/
        });
    </script>