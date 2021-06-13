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
        min-width: 75px;
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
        min-width: 100px;
    }

    .instances .table .th-fixed-s {
        min-width: 50px;
    }

    .instances .table .th-fixed-l {
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

    #index-spinner {
        display:block;
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

<?php

$isLoadScreen = $this->request->query('download') || $this->request->query('create');

?>  

<!-- This will get blocked -->
<?php if (! $isLoadScreen) :  ?>
<script>
    $(document).ready(function() {
        var resourceInterval = setInterval(updateIndex, 5000);
    });
</script>
<?php endif ?>

<div id="connection">
    <div class="d-flex align-items-center">
            <strong><?= __('Connecting') ?>...</strong>
            <div id="index-spinner" class="spinner-border text-primary ml-auto" role="status" aria-hidden="true"></div>
        </div>
    </div>
</div>

<div id="instance-list" class="instances index">
</div>
<script>
    function updateIndex() {
        var geturl = $.get( "/instances/monitor", function(html, status, xhr) {

            // trap logout redirects
            if(xhr.getResponseHeader("X-Action") === 'login'){
                location.reload();
            }
            $('#connection, #connection #index-spinner').hide();
            $("#instance-list").html(html);
        })
        .fail(function( jqXHR, textStatus, errorThrown ) {
            console.log('error');
            if (typeof resourceInterval !== 'undefined') {
                clearInterval(resourceInterval);
            }
            $('#instance-list').empty();
            $('#connection, #connection #index-spinner').show();
        });
    }

    $('#connection, #connection #index-spinner').show();
    updateIndex();

</script>



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
   
       


    </script>
