
<div class="weeklyreports form large-8 medium-16 columns content float: left">
    <h3><?= __('Edit Weeklyreport') ?></h3>
    <button id="navbutton">
        <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $weeklyreport->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $weeklyreport->id)]
            )
        ?>
    </button>

    <?= $this->Form->create($weeklyreport) ?>
        <?php
            echo $this->Form->input('title');
            echo $this->Form->input('week', array('type' => 'number', 'min' => 1, 'max' => 52, 'style' => 'width: 40%;'));
            echo $this->Form->input('year', array('style' => 'width: 50%;'));
            echo $this->Form->input('meetings', array('type' => 'number', 'min' => 0, 'style' => 'width: 40%;'));
            echo $this->Form->input('reglink', ['label' => 'Requirements link']);
            echo $this->Form->input('problems', array('label' => 'Challenges, issues, etc.'));
            echo $this->Form->input('additional');
			echo $this->Form->button(__('Submit'));
        ?>
    <?= $this->Form->end() ?>
</div>
