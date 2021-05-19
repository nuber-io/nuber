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
<?= $this->renderShared('instance-header') ?>
<div class="row">
    <div class="col-2">
        <?= $this->renderShared('instance-nav') ?>
    </div>
    <div class="col-10">
        
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><?= __('Migrate Instance') ?></h5>
                <p class="card-text">
                <?= __('When you migrate an instance, it will transfer the data to a different host. If an instance is running it will be stopped first and then after the migration has been completed, it will either be started on this host or the remote host, depending if you were copying or moving the instance.')  ?>
                </p>
                <p class="card-text">
                <?= __('<b>Moving</b> transfers the instance and its snapshots to the remote host, the local instance and its data will be destroyed after the instance has been copied to the other server.')  ?>
               
                </p>
                <p class="card-text">
                <?= __('<b>Copying</b> creates a clone of the instance with its snapshots on the remote host but the clone will have a different hardware address.')  ?>
                </p>
                <?php
                echo $this->Form->create(null, [
                    'id' => 'migrateForm',
                    'url' => '/instances/migrate/' . $meta['name']
                ]);
                
                echo $this->Form->control('host', [
                    'label' => false,
                    'options' => empty($hosts) ?  [__('No hosts')] : $hosts,
                    'disabled' => empty($hosts) ? true : null
                ]);

                echo $this->Form->button(__('Move Instance'), [
                    'id' => 'moveButton',
                    'type' => 'button',
                    'class' => 'btn btn-warning',
                    'disabled' => empty($hosts) || $hasVolumes ? true : null
                ]);

                echo $this->Form->hidden('clone', ['id' => true, 'value' => 1]);

                echo $this->Form->button(__('Copy Instance'), [
                    'type' => 'submit',
                    'class' => 'ml-1 btn btn-primary',
                    'disabled' => empty($hosts) || $hasVolumes ? true : null
                ]);

                if ($hasVolumes) {
                    echo $this->Html->tag(
                        'div',
                        '<i class="fas fa-info-circle mr-1"></i>' .
                        $this->Html->tag('small', __('Volumes are attached to this instance, these need to be detached first before you can migrate.')),
                        ['class' => 'mt-2']
                    );
                }

                if ($usingBridgedNetwork) {
                    echo $this->Html->tag(
                        'div',
                        '<i class="fas fa-exclamation-triangle"></i>&nbsp;' .
                        $this->Html->tag('small', __('The instance that you are migrating requires that bridged networking is configured on the remote host, if not, it will not start and moving will fail as it tries to start the instance on the remote host. If you want to move the instance without bridging being setup on the remote host, stop the instance first and then change the network settings after the migration is complete.')),
                        ['class' => 'mt-2']
                    );
                }

                echo $this->Form->end();
                ?>
            </div>
        </div>  
    
    </div>
</div>



<?php
echo $this->render('partials/load-screen', [
    'title' => __('Migrating Instance'),
    'text' => __('Please wait whilst your instance is migrated.')
]);
?>

<script>
$(document).ready(function() {  

    $( "#moveButton" ).click(function( event ) {
        $("#clone").val('0');
        $( "#migrateForm" ).submit();
    });

    $( "#migrateForm" ).submit(function( event ) {
        $("#migrateForm .btn").attr("disabled", true);
        event.preventDefault();
        initializeLoadScreen('#migrateForm');
    });

    function initializeLoadScreen(formSelector) {

        showLoadScreen();

        $.ajax({
            type: "POST",
            url: '<?= '/instances/migrate/' . $meta['name'] ?>',
            data: $(formSelector).serialize(),
            success: function(data) {
                console.log(data);
                location.href = '/instances';
            },
            error: function(xhr) {
                // use redirect here for error handling
                debugError(xhr);
                location.href = '<?= '/instances/migrate/' . $meta['name'] ?>';
            },
            timeout: 0
        })
    }

});
</script>