<?php
/**
 * @var \App\Http\View\ApplicationView $this
 */
$action = $this->request->params('action');
$instance = $this->request->params('args')[0];
?>

<style> 
    .nav-instance .nav-link {
        color:#212529;
    }
</style>
<div class="nav nav-instance flex-column nav-pills" id="tab" role="tablist" aria-orientation="vertical">


    <?php

        $navs = [
            'rename' => __('Rename'),
            'resize' => __('Resize'),
            'ports' => __('Ports'),
            'networking' => __('Networking'),
            'console' => __('Terminal'),
            'volumes' => __('Volumes'),
            'snapshots' => __('Snapshots'),
            'backups' => __('Backups'),
            'migrate' => __('Migrate'),
            'destroy' => __('Destroy')
        ];

        foreach ($navs as $name => $human) {
            echo $this->Html->link($human, ['action' => $name, $instance], [
                'class' => $action === $name ? 'nav-link active' : 'nav-link'
            ]);
        }
    ?>
</div>