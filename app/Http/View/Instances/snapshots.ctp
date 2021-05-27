
<?= $this->renderShared('instance-header') ?>
<div class="row">
    <div class="col-2">
        <?= $this->renderShared('instance-nav') ?>
    </div>
    <div class="col-10">


        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><?= __('Take a Snapshot') ?></h5>
                <p class="card-text"><?= __('You can create a snapshot of your instance, which can be restored quickly and at anytime. It is best to stop the instance first before starting the snapshot.') ?></p>

                <?php
                echo $this->Form->create($snapshotForm, [
                    'id' => 'snapshotForm'
                ]);

                echo $this->Form->control('name', [
                    'label' => false,
                    'placeholder' => $meta['name'],
                    'after' => '<small class="form-text text-muted"> ' . __('Lowercase alphanumeric characters and dashes only, with a length between 2-62 characters.') .  '</small>',
                    'regex' => '^[a-z][a-z0-9-]{1,61}$',
                    'required' => true,
                    'default' => $snapshot
                ]);

                echo $this->Form->button(__('Create Snapshot'), ['type' => 'submit', 'class' => 'btn btn-primary']);
                echo $this->renderShared('spinner', ['class' => 'btn-spinner']);

                echo $this->Form->end();
                ?>
            </div>
        </div><!-- card -->

        <div class="card mt-2">
            <div class="card-body">
                <h5 class="card-title"><?= __('Snapshots') ?></h5>
                <p class="card-text"><?= __('These are the snapshots that you have taken, from here you can restore or delete them.') ?></p>

                <ul class="list-group snapshots-list mt-2">
                
                    <?php foreach ($snapshots as $snapshot) : ?>
                        <li id="snapshot-<?= $snapshot['name'] ?>" class="list-group-item d-flex justify-content-between align-items-center">
                            <span><?= $snapshot['name'] ?></span>
                            <span><?= $snapshot['size'] === 0 ? __('pending') : $this->Number->readableSize($snapshot['size']) ?></span>
                            <span><?= $this->Date->timeAgoInWords($this->LxdInstance->convertISOdate($snapshot['created_at'])) ?></span>
                            <div>
                                <a href="#" title="<?= __('Restore snapshot') ?>" onclick="restoreSnapshot('<?= $meta['name'] . "','" . $snapshot['name'] ?>')"><i class="ml-2 fas fa-undo"></i></a>
                                <a href="#" title="<?= __('Delete snapshot') ?>" onclick="deleteSnapshot('<?= $meta['name'] . "','" . $snapshot['name'] ?>')"><i class="ml-2 fas fa-times"></i></a>
                            </div>


                        </li>
                    <?php endforeach ?>
                </ul>
            </div>
        </div><!-- snapshotlist -->
        

    </div>
</div>


<?php
echo $this->render('partials/load-screen', [
    'title' => __('Restoring from Snapshot'),
    'text' => __('Please wait whilst your instance is restored.')
]);
?>


<script> 

$(document).ready(function() {
    initializePillForm('snapshotForm');
});

function restoreSnapshot(instance, snapshot) {

    var body= '<p><?= __('Are you sure you want to restore <strong>{to}<\/strong> using <strong>{from}<\/strong>?') ?><\/p> <p> <span class="badge badge-warning" style="vertical-align:middle"><?= __('Warning') ?><\/span> <?= __('Your instance and data will be overwritten with an older image.') ?> <\/p>';

    confirmDialog({
        "id": "snapshot-restore-modal",
        "title": "<?= __('Restore Instance') ?>",
        "body": body.replace('{to}',instance).replace('{from}',snapshot),
        "ok": "<?= __('Restore Instance') ?>",
        "cancel": "<?= __('Cancel') ?>",
        "okClass": "btn btn-warning"
    }, function () {
            
        showLoadScreen();

        $.ajax({
            type: "POST",
            url: "/snapshots/restore/" + instance + '/' + snapshot,
            data: {},
            success: function(data) {
                console.log(data);
                location.href = "/instances/snapshots/" + instance + '/';
            },
            error: function(xhr) {
                hideLoadScreen();
                alertError('<?= __('Error restoring from snapshot') ?>');
                debugError(xhr);
            },
            timeout: 0
        });
    });
}

function deleteSnapshot(instance, snapshot) {
    confirmDialog({
            "id": "snapshot-delete-modal",
            "title" : "<?= __('Delete Snapshot') ?>",
            "body": "<p><?= __('Are you sure you want to delete this Snapshot, data will be lost?') ?><\/p>",
            "ok": "<?= __('Delete Snapshot') ?>",
            "cancel": "<?= __('Cancel') ?>",
            "okClass": "btn btn-danger"
        }, function () {
            showSpinner();
            $.delete("/snapshots/delete/" + instance + '/' + snapshot, function(data) {
                console.log(data);
                window.location.reload();
            }).always(function(){
                hideSpinner();
            }).fail(function(xhr) {
                alertError('<?= __('Error deleting snapshot') ?>');
                debugError(xhr);
            });
         });
}
</script>


