
<div class="weeklyhours form large-8 medium-16 columns content float: left">
    <?= $this->Form->create($weeklyhour) ?>
    <fieldset>
        <legend><?= __('Add Weeklyhour') ?></legend>
        <?php
            echo $this->Form->input('weeklyreport_id', ['options' => $weeklyreports]);
            echo $this->Form->input('member_id', ['options' => $members]);
            echo $this->Form->input('duration');
			echo $this->Form->button(__('Submit'));
        ?>
    </fieldset>
    <?= $this->Form->end() ?>
</div>
