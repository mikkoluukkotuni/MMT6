<!-- 
    If the lower navigation bar needed, links go here
    
    <ul class="side-nav">
        
    </ul>
-->

<div class="users form large-8 medium-16 columns content float: left">
    <h1>Login</h1>
    <?= $this->Form->create() ?>
    <?= $this->Form->input('email') ?>
    <?= $this->Form->input('password') ?>
    <?= $this->Form->button('Login') ?>
    <?= $this->Form->end() ?>
</div>