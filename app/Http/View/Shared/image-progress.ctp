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

$class = '';
$message = __($task['description']);

if (empty($task['err'])) {
    $string = $task['metadata']['download_progress'] ?? '';
    $percent = 0;
    if (preg_match('/(?<percent>[0-9]{1,3}+)%/i', $string, $matches)) {
        $percent = $matches['percent'];
    }
} else {
    $class = ' bg-danger';
    $percent = 100;
    $message = $task['err'];
}
$id = 'progress-' . uid();
?>
<tr>
    <td colspan="1">
        <?= $message ?>
    </td>
    <td colspan="4">
        <div class="progress">
            <div id='<?= $id ?>' class="progress-bar<?= $class ?>" role="progressbar" style="width: <?= $percent ?>%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </td>
</tr>

<script>
    $(document).ready(function() {
        <?= $percent == 100 ? 'clearProgressbar();'  : 'initProgressbar();' ?>
    });
    function initProgressbar() {
        window.setInterval(function() {
            $.getJSON("/images/progress/" + '<?= $task['id'] ?>', function(data) {
                console.log(data);
                $('#<?= $id ?>').css("width", data['percent'] + '%');
                if(data['status'] === 'Success'){
                    window.location = '/images'
                }
            }).fail(function() { 
              window.location = '/images'
            });
        }, 1000);
    }
    function clearProgressbar() {
        window.setInterval(function() {
            window.location = '/images';
        }, 5000);
    }
</script>