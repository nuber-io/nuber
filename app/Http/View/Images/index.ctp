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
.spinner-border {
    display:none;
}
</style>
<div class="header">
    <div class="float-right">
        <a href="/images/download" class="btn btn-primary"><?= __('Add from Remote') ?></a>
    </div>
    <h2><?= __('Images') ?>
        &nbsp;
        <small class="text-muted"><?= Lxd::hosts()[$this->Session->read('Lxd.host')] ?></small>
   </h2>
    <hr>
    </hr>
</div>
<p><?= __('This is your local image store, images here can be used to create instances without having to download them again.') ?> </p>

<div class="images index">
    <table class="table table-borderless">
        <thead>
            <tr>

                <th scope="col"><?= __('Name') ?></th>
                <th scope="col"><?= __('OS') ?></th>
                <th scope="col"><?= __('Version') ?></th>
                <th scope="col"><?= __('Type') ?></th>
                <th scope="col"><?= __('Size') ?></th>
                <th scope="col" ><?= __('Fingerprint') ?></th>
                <th scope="col"><?= __('Created') ?></th>
                <th scope="col"><?= __('Action') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($images as $image) : ?>
                <?php $id = 'image-' . uid(); ?>
                <tr id="<?= $id ?>">
                    <td><?= $image['aliases'][0]['name'] ?? $image['properties']['description'] ?></th>
                    <td><?= $image['properties']['os'] ?></td>
                    <td><?= $image['properties']['release']  ?></td>
                    <td><?= $image['properties']['type'] === 'squashfs' ? __('Container') : ('Virtual machine')  ?></td>
                    <td><?= $this->Number->readableSize($image['size']) ?></td>
                    <td class="fingerprint">
                        <span> <?= substr($image['fingerprint'], 0, 12) ?></span>
                        <div class="spinner-border text-primary " style="width: 2rem; height: 2rem;" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </td>
                    <td><?= $this->Date->timeAgoInWords($this->LxdInstance->convertISOdate($image['created_at'])) ?></td>
                    <td>

                        <div class="dropdown">
                            <a class="btn btn-light dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <?= __('Actions') ?>
                            </a>

                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                <a class="dropdown-item" href="#" onclick="deleteImage('<?= $image['fingerprint'] ?>','<?= $id ?>')"><?= __('Delete') ?></a>
                            </div>
                        </div>

                    </td>
                </tr>
            <?php endforeach ?>
            
            <?php if (empty($images) && empty($runningOperations)) { ?>
                <tr><td colspan="8"><?= __('No Images') ?></td></tr>
            <?php }

            foreach ($runningOperations as $task) {
                if ($task['description'] === 'Downloading image') {
                    echo $this->renderShared('image-progress', ['task' => $task]);
                }
            }
            ?>
        </tbody>
    </table>
</div>


<div class="card">
  <div class="card-body">
    <h5 class="card-title"><?= __('Create your own Image')?></h5>
    <p class="card-text"><?= __('You can create an image from one of your existing instances, this is ideal where you have setup and installed applications or services, then
    in future you can just create a new instance that is already configured.') ?></p>
    <a href="/images/create" class="btn btn-primary"><?= __('Create Image') ?></a>
  </div>
</div>

<script>
function deleteImage(image,id) {
    confirmDialog({
            "id": "image-delete-modal",
            "title" : "<?= __('Delete image') ?>",
            "body": "<p><?= __('Are you sure you want to delete this image, data will be lost?') ?><\/p>",
            "ok": "<?= __('Delete image') ?>",
            "cancel": "<?= __('Cancel') ?>",
            "okClass": "btn btn-danger"
        }, function () {
            $('#' + id + ' .fingerprint span').hide();
            $('#' + id + ' .fingerprint .spinner-border').show();
     
            $.delete("/images/delete/" + image, function(data) {
                $('#' + id).remove();
                location.href = '/images';
            }).always(function(){
                $('#' + id + ' .fingerprint span').show();
                $('#' + id + ' .fingerprint .spinner-border').hide();
            }).fail(function(xhr) {
                alertError('<?= __('Error deleting image') ?>');
                debugError(xhr);
            });
         });
}
</script>