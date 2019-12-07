
<div class="worktypes form large-8 medium-16 columns content float: left">
    <h3><?= __('Edit Worktype') ?></h3>
    <button id="navbutton">
        <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $worktype->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $worktype->id)]
            )
        ?>
    </button>

    <?= $this->Form->create($worktype) ?>
        <?php
            echo $this->Form->input('description');
			echo $this->Form->button(__('Submit'));
        ?>
    <?= $this->Form->end() ?>
</div>
