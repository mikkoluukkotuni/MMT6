
<div class="projects view large-7 medium-16 columns content float: left">
    <h3><?= h($project->project_name) ?></h3>
    <?php
        $admin = $this->request->session()->read('is_admin');
        $supervisor = ( $this->request->session()->read('selected_project_role') == 'supervisor' ) ? 1 : 0;
        $manager = ( $this->request->session()->read('selected_project_role') == 'manager' ) ? 1 : 0;		
            		            
        if($admin || $supervisor || $manager) { ?>
            <button id="navbutton"><?= $this->Html->link(__('Edit Project'), ['action' => 'edit', $project->id]) ?> </button>
            <?php }            
        if ($admin) { ?>   
            <button id="managing_button"><?= $this->Html->link(__('Metrics'), ['controller' => 'Metrics', 'action' => 'index']) ?> </button>
        <?php }
    ?>
	<p>
		<?= h($project->description) ?>
    </p>
    <table class="vertical-table">
        <tr>
            <th><?= __('Starting Date') ?></th>
            <td><?= h($project->created_on->format('d.m.Y')) ?></tr>
        </tr>
        <tr>
            <th><?= __('Updated On') ?></th>
            <td><?php
			if ($project->updated_on != NULL)
				echo h($project->updated_on->format('d.m.Y'));
			?></tr>
        </tr>
        <tr>
            <th><?= __('Estimated Completion Date') ?></th>
            <td><?php
			if ($project->finished_date != NULL)
				echo h($project->finished_date->format('d.m.Y')); 
			?></tr>
        </tr>
        <tr>
            <th><?= __('Customer') ?></th>
            <td><?php
			if ($project->customer != NULL)
				echo h($project->customer); 
			?></tr>
        </tr>
        <tr>
            <th><?= __('Is Public') ?></th>
            <td><?= $project->is_public ? __('Yes') : __('No'); ?></td>
         </tr>
    </table>
    <!-- only manager, supervisor and admin can see Slack and Trello links -->
	<?php if (in_array($this->request->session()->read('selected_project_role'),['manager','admin','supervisor'])): ?>
        <h4><?= h("Project's connection settings:") ?></h3>
        <table class="vertical-table">
            <tr>
                <th><?= $this->Html->link(__('Slack'), ['controller' => 'Slack', 'action' => 'index']) ?></th>
            </tr>
            <tr>
                <th><?= $this->Html->link(__('Trello'), ['controller' => 'Trello', 'action' => 'index']) ?></th>
            </tr>
            <tr>
                <th><?= $this->Html->link(__('GitHub'), ['controller' => 'Git', 'action' => 'index']) ?></th>
            </tr>
        </table>
    <?php endif; ?> <!-- end if manager/supervisor/admin -->
</div>