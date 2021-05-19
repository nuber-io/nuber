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
<style >
    #retain {
        max-width:100px;
    }
    .scheduled-backup-frequency {
        width:100px;
    }
</style>
<?= $this->renderShared('instance-header') ?>
<div class="row">
    <div class="col-2">
        <?= $this->renderShared('instance-nav') ?>
    </div>
    <div class="col-10">

        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><?= __('Backups') ?></h5>
                <p class="card-text"><?= __('You can enable automatic backups to reguarly create backups using snapshots.') ?></p>
                
                <?php
                echo $this->Form->create($automatedBackup, [
                    'id' => 'backupForm'
                ]);
                echo $this->Form->control('frequency', [
                    'options' => [
                        'hourly' => __('Hourly'),
                        'daily' => __('Daily'),
                        'weekly' => __('Weekly'),
                        'monthly' => __('Monthly')
                    ],
                    'after' => '<small class="form-text text-muted"> ' .
                     __('How often backups should be taken. Daily, weekly, and monthly will be taken at 00:00 for each period.') .
                      '</small>'
                ]);

                echo $this->Form->control('retain', [
                    'label' => __('Keep'),
                    'after' => '<small class="form-text text-muted"> ' . __('Set how many backups that you want to retain for this schedule.') .  '</small>'
                ]);
                echo $this->Form->button(__('Schedule backup'), [
                    'type' => 'submit',
                    'class' => 'btn btn-primary',
                   
                ]);
                echo $this->Form->end();
                ?>
            </div>
        </div>


 

        <div class="card mt-2">
            <div class="card-body">
                <h5 class="card-title"><?= __('Scheduled Backups') ?></h5>
                <p class="card-text">
                    <?php
                        if (empty($scheduledBackups)) {
                            echo __('You have not scheduled any backups.');
                        } else {
                            echo __('These are the backups that you have scheduled.');
                        }

                    ?>
    
                </p>

                <ul class="list-group scheduled-backups-list mt-2">
                    <?php foreach ($scheduledBackups as $backup) : ?>
                        <li id="automated-backup-<?= $backup->id ?>" class="list-group-item d-flex justify-content-between align-items-center">
                            
                            <span class="scheduled-backup-frequency"><?=  $this->LxdInstance->frequency($backup->frequency) ?></span>
                            <span><?= __('Keep {count} backup|Keep {count} backups', ['count' => $backup->retain])  ?></span>
                            <div>
                                <a href="#" title="<?= __('Delete schedule') ?>" onclick="deleteAutomatedBackup(<?= "'{$backup->id}'" ?>)"><i class="ml-2 fas fa-times"></i></a>
                            </div>
                        </li>
                    <?php endforeach ?>
                </ul>
            </div>
        </div>
    
        <div class="card mt-2">
            <div class="card-body">
                <h5 class="card-title"><?= __('Backups') ?></h5>
                <p class="card-text"><?= __('These are the backups of the instance.') ?></p>

                <ul class="list-group backups-list mt-2">
                    <?php foreach ($backups as $backup) : ?>
                        <li id="backup-<?= $backup['name'] ?>" class="list-group-item d-flex justify-content-between align-items-center">
                            
                            <span><?= $this->Date->format($this->LxdInstance->convertISOdate($backup['created_at']), 'Y-m-d H:i') ?></span>
                            <span><?= $this->LxdInstance->frequency($this->LxdInstance->backupFrequency($backup['name'])) ?> </span>
                            
                            <div>
                                <a href="#" title="<?= __('Restore backup') ?>" onclick="restoreBackup(<?= "'{$meta['name']}', '{$backup['name']}'" ?>)"><i class="ml-2 fas fa-undo"></i></a>
                                <a href="#" title="<?= __('Delete backup') ?>" onclick="deleteBackup(<?= "'{$meta['name']}', '{$backup['name']}'" ?>)"><i class="ml-2 fas fa-times"></i></a>
                            </div>
                        </li>
                    <?php endforeach ?>
                </ul>
            </div>
        </div>

    </div>
</div>


<?php
echo $this->render('partials/load-screen', [
    'title' => __('Restoring from Backup'),
    'text' => __('Please wait whilst your instance is restored.')
]);
?>
<script>

function deleteAutomatedBackup(id) {
confirmDialog({
        "id": "automated-backup-delete-modal",
        "title" : "<?= __('Delete Scheduled Backup') ?>",
        "body": "<p><?= __('Are you sure you want to delete this scheduled backup?') ?><\/p>",
        "ok": "<?= __('Delete Scheduled Backup') ?>",
        "cancel": "<?= __('Cancel') ?>",
        "okClass": "btn btn-warning"
    }, function () {
        showSpinner();
        $.delete("/automated_backups/delete/" + id , function(data) {
            console.log(data);
            window.location.reload();
        }).always(function(){
            hideSpinner();
        }).fail(function(xhr) {
            alertError('<?= __('Error deleting scheduled backup') ?>');
            debugError(xhr);
        });
     });
}


function restoreBackup(instance, snapshot) {

var body= '<p><?= __('Are you sure you want to restore <strong>{to}<\/strong> using <strong>{from}<\/strong>?') ?><\/p> <p> <span class="badge badge-warning" style="vertical-align:middle"><?= __('Warning') ?><\/span> <?= __('Your instance and data will be overwritten with an older image. Backups taken after the backup that you are restoring will no longer exist.') ?> <\/p>';

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
            location.href = "/instances/backups/" + instance + '/';
        },
        error: function(xhr) {
            hideLoadScreen();
            alertError('<?= __('Error restoring from backup') ?>');
            debugError(xhr);
        },
        timeout: 0
    });
});
}

function deleteBackup(instance, snapshot) {
confirmDialog({
        "id": "snapshot-delete-modal",
        "title" : "<?= __('Delete Backup') ?>",
        "body": "<p><?= __('Are you sure you want to delete this Backup, data will be lost?') ?><\/p>",
        "ok": "<?= __('Delete Backup') ?>",
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