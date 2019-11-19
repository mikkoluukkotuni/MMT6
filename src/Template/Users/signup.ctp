
<!-- 
    If the lower navigation bar needed, links go here
    
    <ul class="side-nav">
        
    </ul>
-->

<div class="users form large-8 medium-16 columns content float: left">
    <?= $this->Form->create($user) ?>
    <fieldset>
        <legend><?= __('Add User') ?></legend>
            <?php 
            echo $this->Form->input('email');
            echo $this->Form->input('password');
            echo $this->Form->input('first_name');
            echo $this->Form->input('last_name');
            echo $this->Form->input('phone');
            echo $this->Form->input('checkIfHuman', array('label' => 'Write the sum of 2 + 3', 'required' => true));
            echo $this->Form->input('research_allowed', array('type' => 'checkbox', 'label' => 'My data may be used for research.', 'value' => 1));
            echo $this->Form->button(__('Submit'));
        ?>
    </fieldset>
    <?= $this->Form->end(); ?>
</div>