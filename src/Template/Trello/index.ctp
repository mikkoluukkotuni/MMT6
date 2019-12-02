
<div class="workinghours form large-8 medium-16 columns content float: left">
    <h3>Trello Connection</h3>
    <p>Trello integration allows you to link Trello cards to your project requirements.</p>
    <p>First, you must connect your project to a Trello board. Then, you must connect a Trello list to each four requirements.</p>
    <p>Once you complete this, you will be able to get requirement numbers from Trello when preparing the weekly report.</p>
    <?php if($trello == null): ?>
    <a class="trello-link-box" href="<?= $this->Url->build(['controller' => 'Trello', 'action' => 'add']) ?>">
        Add Trello Connection  
    </a>
    <?php else: ?>
    <a class="trello-link-box" href="<?= $this->Url->build(['controller' => 'Trello', 'action' => 'edit', $trello->id]) ?>">
        Edit Trello Connection  
    </a>
    <a class="trello-link-box" href="<?= $this->Url->build(['controller' => 'Trello', 'action' => 'links', $trello->id]) ?>">
        Configure Trello Requirements 
    </a>
    <?php endif; ?>
</div>
