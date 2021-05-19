
<style> 
   /*
    Not clear 
    a .fas.fa-eject {
        color: var(--warning);
    }*/
</style>
<?= $this->renderShared('instance-header') ?>
<div class="row">
    <div class="col-2">
        <?= $this->renderShared('instance-nav') ?>
    </div>
    <div class="col-10">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><?= __('Attach a Volume') ?></h5>
                <p class="card-text"><?= __('You can attach a volume to this instance, then detach when finished.') ?></p>

                <?php
                echo $this->Form->create($attachVolumeForm, [
                    'id' => 'volumeForm'
                ]);

                echo $this->Form->control('name', [
                    'label' => __('Volume'),
                    'options' => empty($volumes) ?  [__('No Volumes')] : $volumes,
                    'disabled' => empty($volumes) ? true : null
                ]);

                echo $this->Form->control('path', [
                    'label' => __('Path'),
                    'after' => '<small class="form-text text-muted">'. __('Set the path where this will be mounted, e.g. /mnt/block-storage') .'</small>',
                    'default' => '/mnt/block-storage',
                    //'regex' => '^\/([a-zA-Z0-9-_\/]){1,}$',
                    'required' => true
                ]);

                echo $this->Form->button(__('Attach Volume'), [
                    'type' => 'submit',
                    'class' => 'btn btn-primary',
                    'disabled' => empty($volumes) ? true : null
                ]);
                echo $this->renderShared('spinner', ['class' => 'btn-spinner']);
                echo $this->Form->end();
                ?>
            </div>
        </div><!-- card -->

        <div class="card mt-2">
            <div class="card-body">
                <h5 class="card-title"><?= __('Volumes') ?></h5>
                <p class="card-text"><?= __('These are the volumes that are attached to this instance') ?></p>
                <ul class="list-group volume-list mt-2">
                
                
                <?php foreach ($meta['devices'] ?? [] as $deviceName => $device) : ?>
                    <?php
                        if (substr($deviceName, 0, 3) !== 'bsv') {
                            continue;
                        }
                    
                    ?>
                    <li id="device-<?= $deviceName ?>" class="list-group-item d-flex justify-content-between align-items-center">
                        <span style="width:40%"><?= $device['source'] ?></span>
                        <span><?= $sizes[$device['source']] ?></span>
                        <span style="width:40%"><?= $device['path'] ?></span>
                        <div>
                            <a href="#" title="<?= __('Detach') ?>" onclick="detachVolume('<?= $meta['name'] . "','" . $deviceName ?>')">
                                <i class="ml-2 fas fa-eject"></i>
                            </a>
                        </div>


                    </li>
                <?php endforeach ?>
        
            </ul>
            </div>
        </div>

    
    </div>
</div>

<script> 
    $(document).ready(function() {
         initializePillForm('volumeForm');
    });

    function detachVolume(instance, volume) {
        confirmDialog({
            "title" : "<?= __('Detach Volume') ?>",
            "body": "<p><?= __('Are you sure you want to detach the volume?') ?><\/p>",
            "ok": "<?= __('Detatch volume') ?>",
            "cancel": "<?= __('Cancel') ?>",
            "okClass": "btn btn-warning"
        }, function () {
            showSpinner();
            $.post("/volumes/detach/" + instance + '/' + volume, function(data) {
                console.log(data);
                window.location.reload();
            }).always(function(){
                hideSpinner();
            }).fail(function(xhr) {
                alertError('<?= __('Error detaching volume') ?>');
                debugError(xhr);
            });
         });
    }
</script>