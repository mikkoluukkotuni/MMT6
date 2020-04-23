
<div class="workinghours form large-8 medium-16 columns content float: left">
    <h3>GitHub Connection</h3>
    <p>GitHub integration allows you to automatically get total commit count of the project's master branch.</p>
    <p>First, you must add your project's repository name, repository owner's GitHub username and repository owner's token.</p>
    <p>Once you complete this, you will be able to get commit number from GitHub when preparing the weekly report.</p>
    <?php if($git == null): ?>
    <a class="trello-link-box" href="<?= $this->Url->build(['controller' => 'Git', 'action' => 'add']) ?>">
        Add GitHub Connection  
    </a>
    <?php else: ?>
    <a class="trello-link-box" href="<?= $this->Url->build(['controller' => 'Git', 'action' => 'edit', $git->id]) ?>">
        Edit GitHub Connection  
    </a>
    <a class="trello-link-box" href="<?= $this->Url->build(['controller' => 'Git', 'action' => 'delete', $git->id]) ?>">
        Delete GitHub Connection  
    </a>
    <?php endif; ?>
</div>
