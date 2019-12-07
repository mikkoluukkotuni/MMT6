
<div class="users form large-8 medium-16 columns content float: left">
    <?= $this->Form->create() ?>
        <h3><?= __('Write your email') ?></h3>
            <?php 
            echo $this->Form->input('email');
            echo $this->Form->button(__('Submit'));
        ?>
    <?= $this->Form->end(); ?>
</div>
