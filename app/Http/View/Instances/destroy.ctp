
<?= $this->renderShared('instance-header') ?>
<div class="row">
    <div class="col-2">
        <?= $this->renderShared('instance-nav') ?>
    </div>
    <div class="col-10">

        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><?= __('Destroy Instance') ?></h5>
                <p class="card-text"><?= __('Before you can destroy an instance, you will need to stop it first.') ?></p>
              
                <?= $this->Form->create(null, [
                    'id' => 'destroyForm'
                ]);  ?>
                <button type="button" class="btn btn-danger" <?= $meta['status'] === 'Stopped' ? '' : 'disabled' ?> onclick="destroyInstance('<?= $meta['name'] ?>')"><?= __('Destroy Instance') ?></button>
           
                <?php
                 
                    echo $this->renderShared('spinner', ['class' => 'btn-spinner']);
                    echo $this->Form->end();
                ?>
            </div>
            
        </div>
    
    </div>
</div>

<script>
function destroyInstance(instance) {

    var body= '<p><?= __('Are you sure you want to destroy <strong>{name}<\/strong>?', ['name' => $meta['name']]) ?><\/p> <p> <span class="badge badge-warning" style="vertical-align:middle"><?= __('Warning') ?><\/span> <?= __('All data for this instance will be deleted and cannot be recovered.') ?> <\/p>';

    confirmDialog({
        "title" : "<?= __('Destroy Instance') ?>",
        "body": body,
        "ok": "<?= __('Destroy Instance') ?>",
        "cancel": "<?= __('Cancel') ?>",
        "okClass": "btn btn-danger"
    }, function () {
        $("#destroyForm .btn").prop('disabled', true);
        $(".btn-spinner").css("display", "inline-block");
        $('#destroyForm').submit();
    });
}
</script>