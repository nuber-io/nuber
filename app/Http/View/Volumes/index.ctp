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

/**
 * @var \App\Http\View\ApplicationView $this
 */
?>
<style> 
.spinner-border {
    display:none;
}
</style>
<div class="header">
    <div class="float-right">
        <a href="/volumes/create" class="btn btn-primary"><?= __('New Volume') ?></a>
    </div>
    <h2><?= __('Volumes') ?>&nbsp;
        <small class="text-muted"><?= Lxd::hosts()[$this->Session->read('Lxd.host')] ?></small>
   </h2>
    <hr>
    </hr>
</div>
<p><?= __('Volumes are storage devices that provide additional storage space to your instances, a volume can only be attached
to one instance at a time, but can also be easily detached.') ?> </p>

<div class="volumes index">
    <table class="table table-borderless">
        <thead>
            <tr>
                <th scope="col"><?= __('Name') ?></th>
                <th class="th-fixed" scope="col"><?= __('Attached') ?></th>
                <th class="th-fixed" scope="col"><?= __('Size') ?></th>
                <th class="th-actions" scope="col">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($volumes as $volume): ?>
            <?php $id = 'volume-' . uid(); ?>       
            <tr id="<?= $id ?>" >          
                <td><?= h($volume['name']) ?></td>
                <td class="used-by">
                    <?php
                        if ($volume['used_by']) {
                            echo $this->Html->link($volume['used_by']['0'], [
                                'controller' => 'Instances',
                                'action' => 'details',
                                $volume['used_by']['0']
                            ]);
                        }
           
                    ?>
                      <div class="spinner-border text-primary " style="width: 2rem; height: 2rem;" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </td>
                <td><?= h($volume['config']['size'] ?? '') ?></td>
                <td class="actions">
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?= __('Actions') ?>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <?php
                                $show = empty($volume['used_by']);

                                echo $this->Html->link(__('Rename'), ['action' => 'rename', $volume['name']], [
                                    'class' => $show ? 'dropdown-item' : 'dropdown-item disabled',
                                    'aria-disabled' => $show ? 'false' : 'true'
                                ]);

                                echo $this->Html->link(__('Delete'), '#', [
                                    'onclick' => "deleteVolume('{$volume['name']}','{$id}')",
                                    'class' => $show ? 'dropdown-item' : 'dropdown-item disabled',
                                    'aria-disabled' => $show ? 'false' : 'true',
                                    'confirm' => __('Are you sure you want to delete #{id}?', ['id' => $volume['name']])
                                ]);
                            ?>
                        </div>
                    </div>
                </td><!-- actions -->
            </tr>
        <?php endforeach; ?>
        <?php if (empty($volumes)) { ?>
                <tr><td colspan="4"><?= __('No Volumes') ?></td></tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script>
function deleteVolume(volume,id) {
    confirmDialog({
            "id": "volume-delete-modal",
            "title" : "<?= __('Delete volume') ?>",
            "body": "<p><?= __('Are you sure you want to delete this volume, data will be lost?') ?><\/p>",
            "ok": "<?= __('Delete volume') ?>",
            "cancel": "<?= __('Cancel') ?>",
            "okClass": "btn btn-danger"
        }, function () {
            $('#' + id + ' .used-by span').hide();
            $('#' + id + ' .used-by .spinner-border').show();
            $.delete("/volumes/delete/" + volume, function(data) {
                $('#' + id).remove();
                location.href = '/volumes';
            }).always(function(){
                $('#' + id + ' .used-by span').show();
                $('#' + id + ' .used-by .spinner-border').hide();
            }).fail(function(xhr) {
                alertError('<?= __('Error deleting volume') ?>');
                debugError(xhr);
            });
         });
}
</script>