<!-- if second navigation needed
    <ul class="side-nav">
        <li></li>
    </ul>
-->

<div class="workinghours form large-8 medium-16 columns content float: left">
    <fieldset>
        <h3>What is Slack Integration?</h3>
        <p>Slack integration allows you to automatically send slack messages for your reports and comments.</p>
        <p>Once you connect your project to a slack channel with a webhook, weekly report notifications and comments will be sent to that slack channel.</p>
    </fieldset>
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
