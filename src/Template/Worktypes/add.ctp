<!-- 
    If the lower navigation bar needed, links go here
    
    <ul class="side-nav">
        
    </ul>
-->

<div class="worktypes form large-9 medium-18 columns content float: left">
    <?= $this->Form->create($worktype) ?>
    <fieldset>
        <legend><?= __('Add Worktype') ?></legend>
        <?php
            echo $this->Form->input('description');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
