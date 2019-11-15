
<!-- 
    If the lower navigation bar needed, links go here
    
    <ul class="side-nav">
        
    </ul>
-->

<div class="metrics form large-8 medium-16 columns content float: left">
    <?= $this->Form->create($metric) ?>
    <fieldset>
        <legend><?= __('Add Metric') ?></legend>
        <?php
            echo $this->Form->input('metrictype_id', ['options' => $metrictypes]);
            echo $this->Form->input('date');
            echo $this->Form->input('value', array('style' => 'width: 30%;'));
			echo $this->Form->button(__('Submit'));
        ?>
    </fieldset>
    <?= $this->Form->end() ?>
</div>
