<table class="table table-borderless">
    <thead>
        <tr>
            <th scope="col"><?= __('Name') ?></th>
            <th class="th-fixed" scope="col"><?= __('IP address') ?></th>
            <th class="th-fixed-s" scope="col"><?= __('Memory') ?></th>
            <th class="th-fixed-s" scope="col"><?= __('CPU') ?></th>
            <th class="th-fixed-s" scope="col"><?= __('Disk') ?></th>
            <th class="th-fixed-s" scope="col"><?= __('Status') ?></th>
            <th class="th-actions" scope="col">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
    <?php
        foreach ($instances as $instance) {
            echo $this->renderShared('instance-row', ['instance' => $instance]);
        }
        if (empty($instances)) {
            ?>
            <tr>
                <td colspan="6"><?= __('No instances') ?> </td>
            </tr>
    <?php
        }

    ?>
    </tbody>
 </table>