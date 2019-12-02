<!-- if second navigation needed
    <ul class="side-nav">
        <li></li>
    </ul>
-->

<div class="workinghours form large-8 medium-16 columns content float: left">
    <h3>What is Slack Integration?</h3>
    <p>Slack integration allows you to automatically send slack messages for your reports and comments.</p>
     <p>Once you connect your project to a slack channel with a webhook, weekly report notifications and comments will be sent to that slack channel.</p>
    <?= $this->Form->create($slack) ?>
    <h4><?= __('Slack Api Info') ?></h4>
        <?= $this->Form->input('webhookurl'); ?>
         
        <div>
            You can create your webhook url in <a href="https://my.slack.com/services/new/incoming-webhook/" target="_blank">this page</a>. You need to be logged on to Slack.
        </div>
	 <?= $this->Form->button(__('Save'));?>    
    <?= $this->Form->end() ?>
</div>
