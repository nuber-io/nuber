
<?= $this->renderShared('instance-header') ?>
<style >
    #memory,#disk,#cpu {
        max-width:100px;
    }
</style>

<div class="row">
    <div class="col-2">
        <?= $this->renderShared('instance-nav') ?>
    </div>
    <div class="col-10">

        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><?= __('Forward Traffic') ?></h5>
                <p class="card-text"><?= __('Forward traffic external traffic to this instance. The port must not be in use by another running instance.') ?></p>
                <?= $this->Form->create($forwardTrafficForm, [
                    'id' => 'portsForm'
                ]) ?>

                <div class="form-row">
                    <?php
                        echo $this->Form->control('listen', [
                            'label' => __('Listen (external)'),
                            'type' => 'text', 'placeholder' => 8000, 'div' => 'form-group col-sm-3 text',
                            'required' => true,
                            'digits' => 'true',
                            'regex' => '^([0-9]{1,4}|[1-5][0-9]{4}|6[0-4][0-9]{3}|65[0-4][0-9]{2}|655[0-2][0-9]|6553[0-5])$',
                            'maxlength' => 5
                        ]);
                    ?>
                    <?php
                        echo $this->Form->control('connect', [
                            'label' => __('Forward to (internal)'),
                            'type' => 'text', 'placeholder' => 80, 'div' => 'form-group col-sm-3 text',
                            'required' => true,
                            'digits' => 'true',
                            'regex' => '^([0-9]{1,4}|[1-5][0-9]{4}|6[0-4][0-9]{3}|65[0-4][0-9]{2}|655[0-2][0-9]|6553[0-5])$',
                            'maxlength' => 5
                        ]);

                        /**
                         * TODO: add proxy_protocol=true option, there is also another option bind=instance which is to
                         * expose service on host to contianer
                         * @link https://lxd.readthedocs.io/en/latest/instances/#type-proxy
                         */
                    ?>
                </div>
                <?php

                    $nicType = $meta['expanded_devices']['eth0']['nictype'] ?? null;
                    $parent = $meta['expanded_devices']['eth0']['parent'] ?? null;

                    $enable = $nicType === 'bridged' && $parent !== 'nuber-bridged';

                    echo $this->Form->button(__('Forward Traffic'), [
                        'type' => 'submit',
                        'class' => 'btn btn-primary',
                        'disabled' => ! $enable
                    ]);

                    echo $this->renderShared('spinner', ['class' => 'btn-spinner']);
                    echo $this->Form->end();

                    if (! $enable) {
                        /**
                         * No need for port forwarding if making the instance visible on the host or the internet.
                         * This is being done to prevent problems or issues.
                         */
                        echo $this->Html->tag(
                            'div',
                            '<i class="fas fa-info-circle mr-1"></i>' .
                            $this->Html->tag('small', __('This is feature is disabled as you are using macvlan or a network bridge.')),
                            ['class' => 'mt-2']
                        );
                    }
                ?>
            </div>
        </div><!-- card -->

        <div class="card mt-2">
            <div class="card-body">
                <h5 class="card-title"><?= __('Configured Ports') ?></h5>
                <p class="card-text"><?= __('These are the ports that have been opened for this instance.') ?></p>

                <ul class="list-group ports-list mt-2">
                    <?php
                    $proxies = [];
                    foreach ($meta['devices'] as $deviceName => $device) {
                        if ($device['type'] === 'proxy') {
                            list($protocol, $ip, $listen) = explode(':', $device['listen']);
                            list($protocol, $ip, $connect) = explode(':', $device['connect']); ?>
                            <li id="device-<?= $deviceName ?>" class="list-group-item d-flex justify-content-between align-items-center">

                                <?= __('Forward traffic from port') . ' ' . $listen . ' to ' . $connect ?>
                                <a href="#" onclick="deletePort('<?= $meta['name'] . "','" . $deviceName ?>')">
                                    <i class="fas fa-times"></i>
                                </a>
                            </li>
                    <?php
                        }
                    }
                    ?>
                </ul>
            </div>
        </div><!-- card -->
    
    </div>
</div>

<script> 
    $(document).ready(function() {
         initializePillForm('portsForm');
    });


    function deletePort(instance, device) {
        confirmDialog({
            "title" : "<?= __('Remove Port Forwarding') ?>",
            "body": "<p><?= __('Are you sure you want to remove this?') ?><\/p>",
            "ok": "<?= __('Remove forwarding') ?>",
            "cancel": "<?= __('Cancel') ?>",
            "okClass": "btn btn-danger"
        }, function () {
            showSpinner();
            $.delete("/devices/delete/" + instance + '/' + device, function(data) {
                console.log(data);
                window.location.reload();
            }).always(function(){
                hideSpinner();
            }).fail(function(xhr) {
                alertError('<?= __('Error removing forwarding') ?>');
                debugError(xhr);
            });
        });
    }
</script>