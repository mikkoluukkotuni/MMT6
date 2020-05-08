
<?php if ($this->request->session()->read('is_admin') || $this->request->session()->read('is_supervisor')) { ?>
    <div class="statistics">
        <h3><?= __('Edit limits') ?></h3> 
        <?= $this->Form->create() ?>
        <div id="chart-limits">
        <?php
            echo $this->Form->input('weekmin', array('type' => 'number', 'value' => $this->request->session()->read('statistics_limits')['weekmin']));
            echo $this->Form->input('weekmax', array('type' => 'number', 'value' => $this->request->session()->read('statistics_limits')['weekmax']));
            echo $this->Form->input('year', array('type' => 'number', 'value' => $this->request->session()->read('statistics_limits')['year']));
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
                    $year = $this->request->session()->read('statistics_limits')['year'];
                    
                    // correction for nonsensical values
                    if ( $min < 1 )  $min = 1;
                    if ( $min > 53 ) $min = 53;
                    if ( $max < 1 )  $max = 1;
                    if ( $max > 53 ) $max = 53;
                    if ( $max < $min ) { 
                        $temp = $max;
                        $max = $min;
                        $min = $temp;
                    }
                    
            /* REMOVED after deemed too restricting. If you want to implement this again, 
                    * find and change this piece of code also in ProjectsController.
            // for clear displaying purposes, amount of columns is limited to 11 (name + 10 weeks)
            if ( ($max - $min) > 9 ) {
                $max = $min + 9;
            } */

                    for ($x = $min; $x <= $max; $x++) {
                        echo "<td>$x</td>";
                    } 
                    ?>
                </tr>
                
                <?php foreach ($projects as $project): ?>
                <tr class="trow">
                    <td class="primary-cell"><?= $this->Html->link(__($project['project_name']), ['action' => 'view', $project['id']]) ?></td>
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
                        } else { 
                            // fetching the ID for current weeklyreport's view-page
                            $query = Cake\ORM\TableRegistry::get('Weeklyreports')
                                    ->find()
                                    ->select(['id'])
                                    ->where(['project_id =' => $project['id'], 
                                            'week >=' => $min, 'year >=' => $year])
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
                            }
                        } ?>
                        </td>
      
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <h4>Metrics</h4>
        <table class="stylized-table">
        <tbody>
            <tr class="header">
                <td style="width:10px;"></td>
                <td style="width:220px;">Project name</td>
                <td>Commits</td>
                <td>Test cases (passed / total)</td>
                <td>Product backlog</td>
                <td>Sprint backlog</td>
                <td>Done</td>
                <td>Risks (high / total)</td>                
            </tr>
            <?php foreach ($projects as $project): ?>
                <tr class="trow">
                    <td
                    <?php if ($project['status'] == 3) {
                            echo(' style="background-color:#ff1100"');
                        } else if ($project['status'] == 2) {
                            echo(' style="background-color:yellow"');                               
                        } else {
                            echo(' style="background-color:#51e064"');
                    } ?> >
                    </td>
                    <td><?= $this->Html->link(__($project['project_name']), ['action' => 'view', $project['id']]) ?></td>
                    <td><?= h($project['metrics'][6]['value']) ?></td>
                    <td><?= h($project['metrics'][7]['value'] . ' / ' . $project['metrics'][8]['value']) ?></td>
                    <td><?= h($project['metrics'][2]['value']) ?></td>
                    <td><?= h($project['metrics'][3]['value']) ?></td>
                    <td><?= h($project['metrics'][4]['value']) ?></td>
                    <td><?= h($project['risks'][0] . ' / ' . $project['risks'][1]) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody> 
    </table>
    <?php }?>

    <h4><?= h('Total numbers of working hours') ?></h4>
    <table class="stylized-table" style="width:640px;">        
        <tbody>
            <tr class="header">
                <td style="width:280px;">Project name</td>
                <td style="width:160px;">Number of members</td>
                <td>Total number of working hours</td>
            </tr>
            <?php foreach ($projects as $project): ?>
                <tr class="trow">
                    <td><?= $this->Html->link(__($project['project_name']), ['action' => 'view', $project['id']]) ?></td>
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
