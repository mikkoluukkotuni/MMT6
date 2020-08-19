<?php echo $this->Highcharts->includeExtraScripts(); ?>
<?php use Cake\I18n\Time; ?>


<div class="members index large-9 medium-18 columns content float: left">
    <h3><?= __('Members') ?></h3>
    <?php
            $admin = $this->request->session()->read('is_admin');
            $supervisor = ( $this->request->session()->read('selected_project_role') == 'supervisor' ) ? 1 : 0;
            // FIX: managers can also add new members
            $manager = ( $this->request->session()->read('selected_project_role') == 'manager' ) ? 1 : 0;
            
            if ($admin || $supervisor || $manager ) {
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
                <th><?= __('Last seen') ?></th>
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
                <?= $userRole = ""; if($member->user->role == 'inactive'){$userRole = "(inactive)";}?>
                <td colspan="2"><?= $member->has('user') ? $this->Html->link($member->user->first_name . " ". $member->user->last_name . " ".$userRole, ['controller' => 'Members', 'action' => 'view', $member->id]) : '' ?></td>  
                <td><?= h($member->project_role) ?></td><?php
                // Get the sum of workinghours for a member who has working hours
                $lastSeen = NULL;              
                if (($member->project_role == 'developer' || $member->project_role == 'manager') && !empty($member->workinghours)) {
                    $query = $member->workinghours;
                    $hours = array();
                    $sum = 0;
                    foreach ($query as $key) {
                        $hours[] = $key->duration;
                        $sum = array_sum($hours);   
                    }

                    // Get the date of member's latest working hour                   
                    $temp = $member->workinghours;
                    usort($temp, function($a, $b) {
                        return $a['date'] <= $b['date'];
                    });
                    $lastSeen = $temp[0]->date;
                }
                else {
                    $sum = 0;
                }
                $total += $sum;
                $target = $member->target_hours;
                if($target == NULL) {
                    $target = 100;
                } ?>
                <td>
                <?php if ($member->project_role == 'developer' || $member->project_role == 'manager') {
                    echo ($sum . ' / ' . $target); 
                } ?></td>
                <td><?php 
                    if ($member->project_role == 'developer' || $member->project_role == 'manager') { 
                        if ($lastSeen != NULL)
                            echo ($lastSeen->format('d.m.Y')); 
                        else
                            echo ('Never');
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
            if (($member->project_role == 'developer' || $member->project_role == 'manager') && $member->target_hours != NULL)
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
                <td><b><?= h($total . ' / ' . $totalTarget) ?></b></td>
                <td></td>
                <td></td>
            </tr> 
            <?php } ?>
        </tbody>
    </table>
    <!-- Only display chart if project has working hours -->
    <?php if ($total > 0) { ?>
    <div class="chart">
        <div id="predictiveProjectChartWrapper">
            <?php echo $this->Highcharts->render($predictiveProjectChart, 'predictiveProjectChart'); ?>
        </div>
    </div> 
    <?php } ?>

    <br>
    <table cellpadding="0" cellspacing="0">
        <tr>
            <th><?= __('Worktype') ?></th>
            <th><?= __('Hours') ?></th>
            <th><?= __('Percentage') ?></th>
        </tr>
    
        <?php
        $queryForTypes = Cake\ORM\TableRegistry::get('Worktypes')
            ->find()
            ->toArray();
           
        foreach($queryForTypes as $type): ?>
            <tr>
                <td><?= h($type->description) ?></td>                
                <td><?= h($hoursByTypeData[$type->id]) ?></td>
                <td>
                <?php 
                if ($hoursByTypeData[$type->id] == 0) {
                    echo(0);
                } else {
                    $percent = round(($hoursByTypeData[$type->id]/$total * 100), 0, PHP_ROUND_HALF_UP);
                    echo($percent);
                }
                ?>
                </td>
            </tr>
        <?php endforeach; ?>   
        
        <tr style="border-top: 2px solid black;">
            <td><b><?= __('Total') ?></b></td> 
            <td><b><?= h($total) ?></b></td>
            <td><b><?= h(100) ?></b></td>
        </tr>    
    </table>        
    
    <?php if ($admin) { ?>
    <a href="<?= $this->Url->build(['controller' => 'Members', 'action' => 'anonymizeAll']) ?>" 
        onclick="return confirm('Are you sure you want to anonymize all members of the project (this cannot be reversed)?');">Anonymize members</a>
    <?php } ?>
</div>  