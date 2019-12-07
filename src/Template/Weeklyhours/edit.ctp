

<div class="weeklyhours form large-8 medium-16 columns content float: left">
    <h3><?= __('Edit Weeklyhour') ?></h3>
    <button id="navbutton">
        <?= $this->Form->postLink(
                    __('Delete'),
                    ['action' => 'delete', $weeklyhour->id],
                    ['confirm' => __('Are you sure you want to delete # {0}?', $weeklyhour->id)]
                )
        ?>
    </button>
    <?= $this->Form->create($weeklyhour) ?>
        <?php
             /* Req 10: changing the ID's of entities to their textual names 
             * updated: WorkingHours.addev.ctp, WorkingHoursController.php, User.php, 
             * Weeklyhours.edit.ctp, WeeklyHoursController.php*/
            //echo $this->Form->input('member_id', ['options' => $members, 'label' => 'Member Name']);
            echo $this->Form->input('duration', array('style' => 'width: 33%;'));
			echo $this->Form->button(__('Submit'));
        ?>
    <?= $this->Form->end() ?>
</div>
