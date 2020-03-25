
<div class="workinghours form large-8 medium-16 columns content float: left">
    <h3>Github Connection</h3>
    <p>Github integration allows you to automatically get total commit count of the project's master branch when preparing the weekly report.</p>
    <p>First, you must save your project's repository name and repository owner's Github username.</p>
    <p>Once you complete this, you will be able to get requirement numbers from Github when preparing the weekly report.</p>
    <?php if($git == null): ?>
    <a class="trello-link-box" href="<?= $this->Url->build(['controller' => 'Git', 'action' => 'add']) ?>">
        Add Github Connection  
    </a>
    <?php else: ?>
    <a class="trello-link-box" href="<?= $this->Url->build(['controller' => 'Git', 'action' => 'edit', $git->id]) ?>">
        Edit Github Connection  
    </a>
    <?php endif; ?>
</div>
