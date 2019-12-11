<!-- 
    If the lower navigation bar needed, links go here
    
    <ul class="side-nav">
        
    </ul>
-->

<div class="users form large-8 medium-16 columns content float: left">
    <h1>Login or <u><?= $this->Html->link(__('Sign up'), ['controller' => 'Users', 'action' => 'signup']) ?></u></h1>
    <?= $this->Form->create() ?>
    <?= $this->Form->input('email') ?>
    <?= $this->Form->input('password') ?>
    <?= $this->Form->button('Login') ?>
    <?= $this->Form->end() ?>
    <br><br><br><br>
    <?= $this->Html->link(__('Forgot Your Password?'), ['controller' => 'Users', 'action' => 'forgotpassword']) ?>
</div>