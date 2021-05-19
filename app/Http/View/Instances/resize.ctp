<?= $this->renderShared('instance-header') ?>
<div class="row">
    <div class="col-2">
        <?= $this->renderShared('instance-nav') ?>
    </div>
    <div class="col-10">

        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><?= __('Resize Instance') ?></h5>
                <p class="card-text"><?= __('Here you can set the limits for the instance.') ?></p>
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

                echo $this->Form->control('disk', [
                    'label' => __('Hard Drive'),
                    'default' => $meta['meta']['storage'],
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

                echo $this->Form->button(__('Resize Instance'), ['type' => 'submit', 'class' => 'btn btn-primary']);
                echo $this->renderShared('spinner', ['class' => 'btn-spinner']);
                echo $this->Form->end();
                ?>
            </div>
        </div>

    </div>
</div>

<script> 
    $(document).ready(function() {
         initializePillForm('resizeForm');
    });
</script>