

<div class="worktypes form large-9 medium-18 columns content float: left">
    <h3><?= __('Add Worktype') ?></h3>

    <?= $this->Form->create($worktype) ?>
        <?php
            echo $this->Form->input('description');
        ?>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
