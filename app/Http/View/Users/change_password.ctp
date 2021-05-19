<?php
/**
 * @var \App\Http\View\ApplicationView $this
 */
?>
<div class="header">
    <h2><?= __('Change Password') ?></h2>
    <hr></hr>
</div>
<div class="user form">
    <?= $this->Form->create($user) ?>
    <?php
        echo $this->Form->control('current_password', ['type' => 'password']);
        echo $this->Form->control('password');
        echo $this->Form->control('password_confirm', ['type' => 'password']);
        echo $this->Form->button(__('Save'), ['type' => 'submit','class' => 'btn btn-primary mr-2']);
        echo $this->Form->end();
    ?>
</div>