<?= $this->renderShared('instance-header') ?>
<div class="row">
    <div class="col-2">
        <?= $this->renderShared('instance-nav') ?>
    </div>
    <div class="col-10">

        <div class="card mb-2">
            <div class="card-body">
                <h5 class="card-title"><?= __('Memory & vCPU') ?></h5>
                <p class="card-text"><?= __('Here you can set the limits for the instance, the instance must be stopped first.') ?></p>
                <?php
                echo $this->Form->create($resizeForm, [
                    'id' => 'resizeForm'
                ]);

                echo $this->Form->control('memory', [
                    'label' => __('Memory Limit'),
                    'default' => $meta['meta']['memory'],
                    'required' => true,
                    'regex' => '^[0-9]{1,5}(MB|GB)$',
                    'after' => '<small class="form-text text-muted"> ' . __('Example format: 512MB or 1GB') .  '</small>'
                ]);

                echo $this->Form->control('cpu', [
                    'label' => __('CPU Limit'),
                    'default' => $meta['meta']['cpu'],
                    'required' => true,
               
                    'regex' => '^[0-9]{1,2}$',
                    'after' => '<small class="form-text text-muted"> ' . __('The maximum number of CPUs to use, e.g 1') .  '</small>'
                ]);

                echo $this->Form->button(__('Resize'), [
                    'type' => 'submit',
                    'class' => 'btn btn-primary',
                    'disabled' => $meta['status'] !== 'Stopped' ? true : null
                ]);
                echo $this->renderShared('spinner', ['class' => 'btn-spinner']);
                echo $this->Form->hidden('form', ['value' => 'memory']);
                echo $this->Form->end();
                ?>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><?= __('Resize Disk') ?></h5>
                <p class="card-text"><?= __('Set the limits for a container or increase the disk space for a virtual machine.') ?></p>
                <?php
                echo $this->Form->create($resizeDiskForm, [
                    'id' => 'increaseDiskForm'
                ]);

                echo $this->Form->control('disk', [
                    'label' => __('Hard Drive'),
                    'default' => $meta['meta']['storage'],
                    'required' => true,
                    'regex' => '^[0-9]{1,5}(MB|GB)$',
                    'after' => '<small class="form-text text-muted"> ' . __('Example format: 512MB or 1GB') .  '</small>'
                ]);

                echo $this->Form->button(__('Resize Disk Space'), [
                    'type' => 'submit',
                    'class' => 'btn btn-primary',
                    'disabled' => $meta['status'] !== 'Stopped' ? true : null
                ]);
                echo $this->renderShared('spinner', ['class' => 'btn-spinner']);
                echo $this->Form->hidden('form', ['value' => 'disk']);
                echo $this->Form->end();
                ?>
            </div>
        </div>

    </div>
</div>

<script> 
    $(document).ready(function() {
         initializePillForm('resizeForm');
         initializePillForm('increaseDiskForm');
    });
</script>