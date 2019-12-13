
<div class="users form large-8 medium-16 columns content float: left">
    <h3>Edit profile</h3>
    <?= $this->Form->create($user) ?>
            <?php
            echo $this->Form->input('email');
            echo $this->Form->input('first_name');
            echo $this->Form->input('last_name');
            echo $this->Form->input('phone');
            echo $this->Form->input('research_allowed', array('options' => array('Disallowed', 'Allowed'),'label' => 'Data usage in research:',
             'hiddenField' => false, 'type' => 'radio'));
            ?>
            <button>Submit</button>
    <?= $this->Form->end() ?>
</div>
<div class="users form large-8 medium-16 columns content float: left">
    <?= $this->Form->create() ?>
        <h3><?= __('Reset Password') ?></h3>
            <?php
            echo $this->Form->hidden('key',['value' => $key]);
            echo $this->Form->input('password',['label' => 'New Password','value' => '', 'type' => 'password', 'required' => true,'empty']);
            echo $this->Form->input('checkPassword',['label' => 'Confirm New Password','value' => '','type' => 'password', 'required' => true,'empty']);
        ?>
        <button>Submit</button>
    <?= $this->Form->end(); ?>
</div>
