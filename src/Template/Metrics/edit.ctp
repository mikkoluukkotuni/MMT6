

<div class="metrics form large-8 medium-16 columns content float: left">
    <h3><?= __('Edit Metric') ?></h3>
    <button id="navbutton">
        <?= $this->Form->postLink(
                __('Delete Metric'),
                ['action' => 'delete', $metric->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $metric->id)]
            )
        ?>
    </button>
    <?php
        $admin = $this->request->session()->read('is_admin');
        if($admin){
        ?>
            <button id="managing_button">
                <?= $this->Form->postLink(
                    __('Delete (admin)'),
                    ['action' => 'deleteadmin', $metric->id],
                    ['confirm' => __('Are you sure you want to delete # {0}?', $metric->id)]
                )
                ?>
            </button>
        <?php
        }
        ?> 
    <?= $this->Form->create($metric) ?>
        <?php
            echo $this->Form->input('value', array('style' => 'width: 30%;'));
			echo $this->Form->button(__('Submit'));
        ?>
    <?= $this->Form->end() ?>
</div>
