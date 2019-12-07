
<div class="metrictypes form large-8 medium-16 columns content float: left">
    <h3><?= __('Edit Metrictype') ?></h3>
    <button id="navbutton">
        <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $metrictype->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $metrictype->id)]
            )
        ?>
    </button>
    <?= $this->Form->create($metrictype) ?>
        <?php
            echo $this->Form->input('id');
            echo $this->Form->input('description');
			echo $this->Form->button(__('Submit'));
        ?>
    <?= $this->Form->end() ?>
</div>
