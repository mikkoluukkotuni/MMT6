<!--
    <ul class="side-nav">
        
    </ul>
-->
<div class="members index large-9 medium-18 columns content float: left">
    <h3><?= __('Members') ?></h3>
    <?php
            $admin = $this->request->session()->read('is_admin');
            $supervisor = ( $this->request->session()->read('selected_project_role') == 'supervisor' ) ? 1 : 0;
            // FIX: managers can also add new members
            $manager = ( $this->request->session()->read('selected_project_role') == 'manager' ) ? 1 : 0;
            
            if($admin || $supervisor || $manager ) {
        ?>
            <button id="navbutton"><?= $this->Html->link(__('+ New Member'), ['action' => 'add']) ?></button>
        <?php } ?>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th class="image-cell"></th>
                <th colspan="2"><?= __('Name') ?></th>
                <th><?= $this->Paginator->sort('project_role') ?></th>
                <th><?= __('Working hours') ?></th>
                <th><?= __('Target hours') ?></th>
                <th class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php $total = 0;?>
            <?php foreach ($members as $member): ?>
            
            <tr>
                <td class="image-cell">
                    <?= $this->Custom->profileImage($member->user_id); ?>
                </td>
                <?= $rooli = ""; if($member->user->role == inactive){$rooli = "(inactive)";}?>
                <td colspan="2"><?= $member->has('user') ? $this->Html->link($member->user->first_name . " ". $member->user->last_name . " ".$rooli, ['controller' => 'Members', 'action' => 'view', $member->id]) : '' ?></td>  
                <td><?= h($member->project_role) ?></td><?php
                // Get the sum of workinghours for a member who has working hours              
                if (!empty($member->workinghours)) {
                    $query = $member->workinghours;
                    $hours = array();
                    $sum = 0;
                    foreach ($query as $key) {
                        $hours[] = $key->duration;
                        $sum = array_sum($hours);   
                    }
                }
                else {
                    $sum = "";
                }
                $total += $sum;?>

                <td><?= h($sum) ?></td>
                <td><?php 
                    if ($member->project_role != 'supervisor' && $member->project_role != 'client') { 
                        if ($member->target_hours != NULL)
                            echo h($member->target_hours); 
                        else
                            echo h('100 (default)');
                    }
                ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $member->id]) ?>
                    <?php
			$admin = $this->request->session()->read('is_admin');
            $supervisor = ( $this->request->session()->read('selected_project_role') == 'supervisor' ) ? 1 : 0;
            //developer can edit own data
            $current_member = ( $this->request->session()->read('selected_project_memberid') == $member->id ) ? 1 : 0;
			if($admin || $supervisor || $current_member){
			        ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $member->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $member->id], ['confirm' => __('Are you sure you want to delete # {0}? Note: You must first delete logged tasks of this member', $member->id)]) ?>
                    <?php } ?>
                </td>
            </tr>
            <?php endforeach; ?>

            <?php
            $totalTarget = 0;
            foreach ($members as $member):
            if ($member->target_hours != NULL)
                $totalTarget= $totalTarget + $member->target_hours;
            if (($member->project_role == 'developer' || $member->project_role == 'manager') && $member->target_hours == NULL)
                $totalTarget = $totalTarget + 100;
            endforeach;
            ?>
            
            <?php if (!empty($member->project_id)) { ?>
            <tr style="border-top: 2px solid black;">
                <td></td>
                <td colspan="2"><b><?= __('Total') ?></b></td>
                <td></td>
                <td><b><?= h($total) ?></b></td>
                <td><b><?= h($totalTarget) ?></b></td>
                <td></td>
            </tr> 
            <?php } ?>
        </tbody>
    </table>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        </ul>
        <p><?= $this->Paginator->counter() ?></p>
    </div>
</div>  