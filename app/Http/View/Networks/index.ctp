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
use Origin\Text\Text;

/**
 * @var \App\Http\View\ApplicationView $this
 */
?>
<style>
.th-fixed {
        min-width: 100px;
    }
</style>

<div class="header">
    <div class="float-right">
        <a href="/networks/create" class="btn btn-primary"><?= __('New Network') ?></a>
    </div>
    <h2>
        <?= __('Networks') ?> &nbsp;
        <small class="text-muted"><?= Lxd::hosts()[$this->Session->read('Lxd.host')] ?></small>
    </h2>
    <hr>
    </hr>
</div>
<?php
    /**
     * The reason only the last network is added can be deleted because we use the format nuberbr<number>, so if user deletes nuberbr1, and then
     * this will be blank, and this could break migration. Keeping the networks/profiles in sync. Using the db would be helpful, but then if the
     * event they reinstall, this information will be lost and would require a bit of configuration.
     */
?>
<p><?= __('Networks are network interfaces that your instances uses to communicate to between each other on the same network. Here you can also configure the address range based upon LAN settings or using a block that your cloud has given to you.') ?> </p>

<?php

    $count = count($networks);
?>
<div class="networks index">
    <table class="table table-borderless">
        <thead>
            <tr>
                <th class="th-fixed" scope="col"><?= __('Name') ?></th>
                <th class="th-fixed" scope="col"><?= __('Instances') ?></th>
                <th class="th-fixed" scope="col"><?= __('IPv4') ?></th>
                <th class="th-fixed" scope="col"><?= __('IPv6') ?></th>
                <th class="th-actions" scope="col">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($networks as $network): ?>

            <?php
                // Count how many instances are using it
                $used = 0;
                if (! empty($network['used_by'])) {
                    $used = collection($network['used_by'])->filter(function ($network) {
                        return Text::contains('/instances/', $network);
                    })->count();
                }
              
            ?>

            <?php $id = 'network-' . uid(); ?>       
            <tr id="<?= $id ?>" >          
    
                <td><?= h($network['name']) ?></td>
                <td><?= $used ?></td>
                <td><?= h($network['config']['ipv4.address'] ?? '') ?></td>
                <td><?= h($network['config']['ipv6.address'] ?? '') ?></td>
                <td class="actions">
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?= __('Actions') ?>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <?php
    
                                echo $this->Html->link(__('Edit'), ['action' => 'edit', $network['name']], [
                                    'class' => 'dropdown-item',
                                    'aria-disabled' => false
                                ]);
                                echo $this->Html->link(__('Delete'), '#', [
                                    'onclick' => "deleteNetwork('{$network['name']}','{$id}')",
                                    'class' => $used === 0 ? 'dropdown-item' : 'dropdown-item disabled',
                                    'aria-disabled' => $used === 0 ? 'false' : 'true',
                                    'confirm' => __('Are you sure you want to delete #{id}?', ['id' => $network['name']])
                                ]);
                               
                            ?>
                        </div>
                    </div>
                </td><!-- actions -->
            </tr>
        <?php endforeach; ?>
        <?php if (empty($networks)) { ?>
                <tr><td colspan="4"><?= __('No Networks') ?></td></tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script>
function deleteNetwork(network,id) {
    confirmDialog({
            "id": "network-delete-modal",
            "title" : "<?= __('Delete network') ?>",
            "body": "<p><?= __('Are you sure you want to delete this network?') ?><\/p>",
            "ok": "<?= __('Delete network') ?>",
            "cancel": "<?= __('Cancel') ?>",
            "okClass": "btn btn-danger"
        }, function () {
            $('#' + id + ' .used-by span').hide();
            $('#' + id + ' .used-by .spinner-border').show();
            $.delete("/networks/delete/" + network, function(data) {
                $('#' + id).remove();
                location.href = '/networks';
            }).always(function(){
                $('#' + id + ' .used-by span').show();
                $('#' + id + ' .used-by .spinner-border').hide();
            }).fail(function(xhr) {
                alertError('<?= __('Error deleting network') ?>');
                debugError(xhr);
            });
         });
}
</script>