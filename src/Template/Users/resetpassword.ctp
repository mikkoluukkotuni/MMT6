<?php if($showForm){ ?>

<!-- 
    If the lower navigation bar needed, links go here
    
    <ul class="side-nav">
        
    </ul>
-->

<div class="users form large-8 medium-16 columns content float: left">
    <?= $this->Form->create() ?>
    <fieldset>
        <legend><?= __('Reset Password') ?></legend>
            <?php
            echo $this->Form->hidden('key',['value' => $key]);
            echo $this->Form->input('password',['label' => 'New Password','value' => '', 'type' => 'password', 'required' => true,'empty']);
            echo $this->Form->input('checkPassword',['label' => 'Confirm New Password','value' => '','type' => 'password', 'required' => true,'empty']);
            echo $this->Form->button(__('Submit'));
        ?>
    </fieldset>
    <?= $this->Form->end(); ?>
</div>

<?php } ?>
