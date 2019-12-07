

<div class="users form large-8 medium-16 columns content float: left">
    <h3><?= __('Edit User') ?></h3>
    <button id="navbutton">
        <?= $this->Form->postLink(
                    __('Delete'),
                    ['action' => 'delete', $user->id],
                    ['confirm' => __('Are you sure you want to delete # {0}?', $user->id)]
                )
        ?>
    </button>
    <?= $this->Form->create($user) ?>
        <?php
            echo $this->Form->input('email');
            echo $this->Form->input('password');
            echo $this->Form->input('first_name');
            echo $this->Form->input('last_name');
            echo $this->Form->input('phone');
            echo $this->Form->input('role', 
                ['options' => array('user' => 'user', 'admin' => 'admin', 'inactive' => 'inactive')]);
			echo $this->Form->button(__('Submit'));
        ?>
    <?= $this->Form->end() ?>
</div>
