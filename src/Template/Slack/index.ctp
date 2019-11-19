
    <ul class="side-nav">
        <li><?= $this->Html->link(__('What is Slack Integration?'), ['controller' => 'Slack', 'action' => 'about']) ?> </li>
    </ul>

<div class="workinghours form large-8 medium-16 columns content float: left">
    <?= $this->Form->create($slack) ?>
    <fieldset>
        <legend><?= __('Slack Api Info') ?></legend>
        <?= $this->Form->input('webhookurl'); ?>
         
        <div>
            You can create your webhook url in <a href="https://my.slack.com/services/new/incoming-webhook/" target="_blank">this page</a>. You need to be logged on to Slack.
        </div>
	 <?= $this->Form->button(__('Save'));?>    
    </fieldset>
    <?= $this->Form->end() ?>
</div>
