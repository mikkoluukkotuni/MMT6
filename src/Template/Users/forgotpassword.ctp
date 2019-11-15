
<!-- 
    If the lower navigation bar needed, links go here
    
    <ul class="side-nav">
        
    </ul>
-->

<div class="users form large-8 medium-16 columns content float: left">
    <?= $this->Form->create() ?>
    <fieldset>
        <legend><?= __('Write your email') ?></legend>
            <?php 
            echo $this->Form->input('email');
            echo $this->Form->button(__('Submit'));
        ?>
    </fieldset>
    <?= $this->Form->end(); ?>
</div>
