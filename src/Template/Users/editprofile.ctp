
<div class="users form large-8 medium-16 columns content float: left">
    <h3>Edit profile</h3>
    <button id="navbutton"><?= $this->Html->link(__('Change Password'), ['action' => 'password']) ?></button>
    <?= $this->Form->create($user) ?>
    <fieldset>
            <?php
            echo $this->Form->input('email');
            echo $this->Form->input('first_name');
            echo $this->Form->input('last_name');
            echo $this->Form->input('phone');
            echo $this->Form->input('research_allowed', array('options' => array('Disallowed', 'Allowed'),'label' => 'Data usage in research:',
             'hiddenField' => false, 'type' => 'radio'));
            echo $this->Form->button(__('Submit'));
            ?>
    </fieldset>
    <?= $this->Form->end() ?>
</div>
