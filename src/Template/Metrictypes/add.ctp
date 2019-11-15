
 <!-- 
    If the lower navigation bar needed, links go here
    
    <ul class="side-nav">
        
    </ul>
-->

<div class="metrictypes form large-8 medium-16 columns content float: left">
    <?= $this->Form->create($metrictype) ?>
    <fieldset>
        <legend><?= __('Add Metrictype') ?></legend>
        <?php
            echo $this->Form->input('description');
			echo $this->Form->button(__('Submit'));
        ?>
    </fieldset>
    <?= $this->Form->end() ?>
</div>
