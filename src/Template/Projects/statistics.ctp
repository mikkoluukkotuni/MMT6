
<?php if ($this->request->session()->read('is_admin') || $this->request->session()->read('is_supervisor')) { ?>
    <div class="statistics">
        <h3><?= __('Edit limits') ?></h3> 
        <?= $this->Form->create() ?>
        <div id="chart-limits">
        <?php
            echo $this->Form->input('weekmin', array('type' => 'number', 'min' => 1, 'max' => 52, 'value' => $this->request->session()->read('statistics_limits')['weekmin']));
            echo $this->Form->input('weekmax', array('type' => 'number', 'min' => 1, 'max' => 52, 'value' => $this->request->session()->read('statistics_limits')['weekmax']));
            echo $this->Form->input('yearmin', array('type' => 'number', 'min' => 2015, 'max' => $time->year, 'value' => $this->request->session()->read('statistics_limits')['yearmin']));
            echo $this->Form->input('yearmax', array('type' => 'number', 'min' => 2015, 'max' => $time->year, 'value' => $this->request->session()->read('statistics_limits')['yearmax']));
        ?>
        </div>
        <button>Submit</button>
        <?= $this->Form->end() ?>
    </div>
<?php } ?>

<div class="projects view large-9 medium-18 columns content float: left">
    <h3><?= h('Statistics') ?></h3>
    
    <?php if ($this->request->session()->read('is_admin') || $this->request->session()->read('is_supervisor')) { ?>
        <h4><?= h('Weekly reports') ?></h4>
        <table class="stylized-table stat-table">
            <tbody>
                <tr class="header">
            <!-- empty cell -->
                    <td class="primary-cell"></td>

                    <?php 
                    $min = $this->request->session()->read('statistics_limits')['weekmin'];
                    $max = $this->request->session()->read('statistics_limits')['weekmax'];
                    $yearmin = $this->request->session()->read('statistics_limits')['yearmin'];
                    $yearmax = $this->request->session()->read('statistics_limits')['yearmax'];
                    
                    // correction for nonsensical values
                //     if ( $min < 1 )  $min = 1;
                //     if ( $min > 53 ) $min = 53;
                //     if ( $max < 1 )  $max = 1;
                //     if ( $max > 53 ) $max = 53;
                //     if ( $max < $min ) { 
                // $temp = $max;
                //         $max = $min;
                //         $min = $temp;
                //     }
                    
            /* REMOVED after deemed too restricting. If you want to implement this again, 
                    * find and change this piece of code also in ProjectsController.
            // for clear displaying purposes, amount of columns is limited to 11 (name + 10 weeks)
            if ( ($max - $min) > 9 ) {
                $max = $min + 9;
            } */

                if($yearmin == $yearmax) {
                    for ($x = $min; $x <= $max; $x++) {
                        echo "<td>$x</td>";
                    }
                } else {
                    for ($x = $min; $x <= 52; $x++) {
                        echo "<td>$x</td>";
                    }
                    for ($x = 1; $x <= $max; $x++) {
                        echo "<td>$x</td>";
                    }
                } 
                    ?>
                </tr>
                
                <?php foreach ($projects as $project): ?>
                <tr class="trow">
                    <td class="primary-cell"><?= h($project['project_name']) ?></td>
                        <?php                    
                $admin = $this->request->session()->read('is_admin');
                $supervisor = ( $this->request->session()->read('selected_project_role') == 'supervisor' ) ? 1 : 0;

                // query iterator, resets after finishing one row
                $i = 0;

                            foreach ($project['reports'] as $report):                          
                                ?>
                            <td>
                            <?php
                            // missing ones print normally
                            if ( $report == '-' ) { ?>
                                <?= h($report) ?>
                            <?php
                                    }
                            // adding link to X's if admin or supervisor
                            // BUG FIX 31.3.: links to weeklyreports now actually link to correct reports
                            elseif ( ($report == 'X' || $report == 'L') && ($admin || $supervisor) ) { 

                                if($yearmin == $yearmax) {
                                    // fetching the ID for current weeklyreport's view-page
                                    $query = Cake\ORM\TableRegistry::get('Weeklyreports')
                                            ->find()
                                            ->select(['id'])
                                            ->where(['project_id =' => $project['id'], 
                                                    'week >=' => $min, 'year >=' => $yearmin])
                                            ->toArray();
                                    // transforming returned query item to integer
                                    $reportId = $query[$i++]->id;
                                        
                                    // X's have normal link color so they echo normally
                                    if ($report == 'X') {
                                        echo $this->Html->link(__($report.' (view)'), [
                                            'controller' => 'Weeklyreports',
                                            'action' => 'view',
                                            $reportId ]);
                                            // unread weeklyreports have some mark indicating it
                                            $userid = $this->request->session()->read('Auth.User.id');
                                            $newreps = Cake\ORM\TableRegistry::get('Newreports')->find()
                                                    ->select()
                                                    ->where(['user_id =' => $userid, 'weeklyreport_id =' => $reportId])
                                                    ->toArray();
                                            if ( sizeof($newreps) > 0 ) {
                                                    echo "<div class='unread'>unread</div>";
                                            }
                                            
                                    } else {
                                        echo $this->Html->link(__($report.' (view)'), [
                                            'controller' => 'Weeklyreports',
                                            'action' => 'view',
                                            $reportId ], ['style'=>'color: orange;']);
                                    } ?>
                                <?php

                                // Weekmin and Weekmax are not on the same year
                                } else {
                                    // fetching the ID for current weeklyreport's view-page
                                    $query = Cake\ORM\TableRegistry::get('Weeklyreports')
                                            ->find()
                                            ->select(['id'])
                                            ->where(['project_id =' => $project['id'], 
                                                    'OR' => [['week >=' => $min, 'year ==' => $yearmin], ['week <=' => $max, 'year ==' => $yearmax]]])
                                            ->toArray();
                                    // transforming returned query item to integer
                                    $reportId = $query[$i++]->id;
                                        
                                    // X's have normal link color so they echo normally
                                    if ($report == 'X') {
                                        echo $this->Html->link(__($report.' (view)'), [
                                            'controller' => 'Weeklyreports',
                                            'action' => 'view',
                                            $reportId ]);
                                            // unread weeklyreports have some mark indicating it
                                            $userid = $this->request->session()->read('Auth.User.id');
                                            $newreps = Cake\ORM\TableRegistry::get('Newreports')->find()
                                                    ->select()
                                                    ->where(['user_id =' => $userid, 'weeklyreport_id =' => $reportId])
                                                    ->toArray();
                                            if ( sizeof($newreps) > 0 ) {
                                                    echo "<div class='unread'>unread</div>";
                                            }
                                            
                                    } else {
                                        echo $this->Html->link(__($report.' (view)'), [
                                            'controller' => 'Weeklyreports',
                                            'action' => 'view',
                                            $reportId ], ['style'=>'color: orange;']);
                                    } ?>
                                <?php
                                }
                            // displays X without a link to other users
                            } else { ?>
                                    <?= h($report) ?>
                            <?php } ?>
                                        </td>
      
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php }?>

    <table class="stylized-table" style="width:640px;">
        <h4><?= h('Total numbers of working hours') ?></h4>
        <tbody>
            <tr class="header">
                <td style="width:280px;">Project name</td>
                <td style="width:160px;">Number of members</td>
                <td>Total number of working hours</td>
            </tr>
            <?php foreach ($projects as $project): ?>
                <tr class="trow">
                    <td><?= h($project['project_name']) ?></td>
                    <td><?= h($project['user_members']) ?></td>
                    <td><?= h($project['sum']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody> 
    </table>
    
    <article>
    	<h4><?= h('Additional statistics') ?></h4>
    	<p>Visit the <a href="https://coursepages.uta.fi/tiea4/paasivu/statistics/" target="_blank">Statistics page</a> of Project Work course.</p>
    </article>
</div>
