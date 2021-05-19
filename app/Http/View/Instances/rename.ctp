<?= $this->renderShared('instance-header') ?>
<div class="row">
    <div class="col-2">
        <?= $this->renderShared('instance-nav') ?>
    </div>
    <div class="col-10">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><?= __('Rename Instance') ?></h5>
                <p class="card-text"><?= __('You can rename your instance, if it is runnning then you need to stop it first.')  ?></p>
                <?php
                echo $this->Form->create($renameForm, [
                    'id' => 'renameForm'
                ]);
                echo $this->Form->control('name', [
                    'label' => false,
                    'placeholder' => $meta['name'],
                    'after' => '<small class="form-text text-muted"> ' . __('Alphanumeric characters and dashes only, with a length between 2-62 characters.') .  '</small>',
                    'regex' => '^[a-z][a-z0-9-]{1,61}$',
                    'required' => true
                ]);

                echo $this->Form->button(__('Rename Instance'), [
                    'type' => 'submit',
                    'class' => 'btn btn-primary',
                    'disabled' => $meta['status'] !== 'Stopped' ? true : null
                ]);
                echo $this->renderShared('spinner', ['class' => 'btn-spinner']);
                echo $this->Form->end();

                ?>
            </div>
        </div>
    </div>
</div>

<script> 
    $(document).ready(function() {
         initializePillForm('renameForm');
    });
</script>