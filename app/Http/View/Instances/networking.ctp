
<?= $this->renderShared('instance-header') ?>
<div class="row">
    <div class="col-2">
        <?= $this->renderShared('instance-nav') ?>
    </div>
    <div class="col-10">

        <div class="card mb-2">
            <div class="card-body">
                <h5 class="card-title"><?= __('Network Interfaces') ?></h5>
                <p class="card-text"><?= __('If you need your instance to be visible on your network or you want to set a public static IP address, then you will need to use either Macvlan or Bridged networking. The eth0 device is configured by default to use DHCP, so changes here may require that you edit the networking settings inside the container.')  ?></p>
                <?php

                    echo $this->Form->create($networkingForm, [
                        'url' => '/instances/networkSettings/' . $this->request->params('args')[0],
                        'id' => 'networkingForm']);

                        ?>

                        <?php
                  
                        // Prevent user from using these
                        if ($hasPorts) {
                            unset($networks['nuber-macvlan'],$networks['nuber-bridged']);
                        }

                        echo $this->Form->control('eth0', [
                            'label' => __('eth0'),
                            'options' => $networks,
                            'value' => $networkingForm->eth0,
                     
                        ]);

                        /**
                         * Current design is that virtual network if available is on eth0 only
                         */
                    
                        $networks = array_intersect_key($networks, array_flip(['nuber-macvlan', 'nuber-bridged']));

                        echo $this->Form->control('eth1', [
                            'label' => __('eth1'),
                            'options' => $networks,
                            'value' => $networkingForm->eth1,
                            'empty' => true
                        ]);

                    echo $this->Form->button(__('Update Network Settings'), [
                        'type' => 'submit',
                        'class' => 'btn btn-warning',
                        'disabled' => $meta['status'] !== 'Stopped'
                    ]);
                    echo $this->renderShared('spinner', ['class' => 'btn-spinner']);
                    echo $this->Form->end();

                    if ($hasPorts) {
                        /**
                         * No need for port forwarding if making the instance visible on the host or the internet.
                         * This is being done to prevent problems or issues.
                         */
                        echo $this->Html->tag(
                            'div',
                            '<i class="fas fa-info-circle mr-1"></i>' .
                            $this->Html->tag('small', __('You currently have port forwarding configured, you will need to remove these before you can enable Macvlan or Bridged networking.')),
                            ['class' => 'mt-2']
                        );
                    }

                    ?>

                    

            </div>
        </div><!-- card -->
    
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><?= __('IP Address') ?></h5>
                <p class="card-text"><?= __('You can set the IP address for this instance on a virtual network. To remove the IP address, clear the field and resubmit. The instance needs to be stopped before changes to the network settings can be made.')  ?></p>

                <?php

                    echo $this->Form->create($ipAddressForm, [
                        'url' => '/instances/ipSettings/' . $this->request->params('args')[0],
                        'id' => 'ipAddressForm'
                    ]);

                        echo $this->Form->control('ip4_address', [
                            'label' => __('IPv4 Address'),
                            'placeholder' => null,
                            'after' => '<small class="form-text text-muted"> ' . __('Set an IPv4 address. e.g. 10.0.0.123. ') .  '</small>',
                            'regex' => '^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$'
                        ]);

                    /**
                     *  Setting static IP addresses only works in DHCPv6 mode, even if you configure the bridge
                     *  you still need to configure each container, which varies between distros
                     *  @see https://discuss.linuxcontainers.org/t/using-static-ips-with-lxd/1291/7
                     */
                    /*

                    echo $this->Form->control('ip6_address', [
                        'label' => __('IPv6 Address'),
                        'placeholder' => null,
                        'after' => '<small class="form-text text-muted"> ' . __('Set an IPv6 address. e.g. fd10:0:0:0:0:0:0:123') .  '</small>',
                        'regex' => '^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$'
                    ]);

                    */
                  
                    $nicType = $meta['expanded_devices']['eth0']['nictype'] ?? null;
                    $parent = $meta['expanded_devices']['eth0']['parent'] ?? null;

                   $canSetIpAddresses = $nicType === 'bridged' && $parent !== 'nuber-bridged';

                    echo $this->Form->button(__('Update IP Settings'), [
                        'type' => 'submit',
                        'class' => 'btn btn-warning',
                        'disabled' => $meta['status'] !== 'Stopped' || ! $canSetIpAddresses ? true : null
                    ]);
                    
                    echo $this->renderShared('spinner', ['class' => 'btn-spinner']);
                    echo $this->Form->end();

                    if (! $canSetIpAddresses) {
                        echo $this->Html->tag(
                            'div',
                            '<i class="fas fa-info-circle mr-1"></i>' .
                            $this->Html->tag('small', __('Setting the IP address is only available when the instance is on a virtual network.')),
                            ['class' => 'mt-2']
                        );
                    }
                    ?>

            </div>
        </div><!-- card -->

    </div>
</div>

<script> 
    $(document).ready(function() {
         initializePillForm('networkingForm');

         eth1Toggle($('#eth0').val());
         $("#eth0").change(function () {
             eth1Toggle($(this).val());
        });
    });

    function eth1Toggle(value){
    
        if( value === 'nuber-macvlan' || value === 'nuber-bridged'){
            $("#eth1").val('');
            $("#eth1").prop('disabled',true);
        }
        else{
          
            $("#eth1").prop('disabled',false);
        }
    }

</script>