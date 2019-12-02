
<div class="workinghours view large-7 medium-14 columns content float: left">
    <h3><?= h("View logged task") ?></h3>
    <?php
        $admin = $this->request->session()->read('is_admin');
        $supervisor = ( $this->request->session()->read('selected_project_role') == 'supervisor' ) ? 1 : 0;
		$manager = ( $this->request->session()->read('selected_project_role') == 'manager' ) ? 1 : 0;
		// the week and year of the last weekly report
		$project_id = $this->request->session()->read('selected_project')['id'];
            if ( ($workinghour->member->user_id == $this->request->session()->read('Auth.User.id')) || $manager || $supervisor || $admin ) { ?>
			    <button id="navbutton"><?= $this->Html->link(__('Edit logged time'), ['action' => 'edit', $workinghour->id]) ?> </button>
		<?php } ?>
    <table class="vertical-table">
        <tr>
            <th><?= __('Member') ?></th>
            <td colspan="2"><?= $workinghour->has('member') ? $this->Html->link($workinghour->member->member_name, ['controller' => 'Members', 'action' => 'view', $workinghour->member->id]) : '' ?></td>
        </tr>
        <tr>
            <th><?= __('Date') ?></th>
            <td colspan="2"><?= h($workinghour->date->format('d.m.Y')) ?></tr>
        </tr>
        <tr>
            <th><?= __('Duration') ?></th>
            <td colspan="2"><?= $this->Number->format($workinghour->duration) ?></td>
        </tr>
        <tr>
            <th><?= __('Worktype') ?></th>
            <td colspan="2"><?= $workinghour->has('worktype') ? $this->Html->link($workinghour->worktype->description, ['controller' => 'Worktypes', 'action' => 'view', $workinghour->worktype->id]) : '' ?></td>
        </tr>
        <tr>
            <th><?= __('Description') ?></th>
            <td colspan="2"><?= h(wordwrap($workinghour->description,35,"\n",TRUE)) ?></td>
        </tr>        
        <?php if($workinghour->created_on != NULL) {?>
            <tr>
                <th><?= __('Created On') ?></th>
                <td colspan="2"><?= h($workinghour->created_on->format('d.m.Y')) ?></tr>
            </tr>
        <?php } ?>
        <?php if($workinghour->modified_on != NULL) {?>
            <tr>
                <th><?= __('Updated On') ?></th>
                <td colspan="2"><?= h($workinghour->modified_on->format('d.m.Y')) ?></tr>
            </tr>
        <?php } ?>
    </table>
</div>
