

<?php
echo $this->Html->css('jquery-ui.min');
echo $this->Html->script('jquery');
echo $this->Html->script('jquery-ui.min');
?>


<div class="workinghours form large-8 medium-16 columns content float: left">
    <h3><?= __('Log time') ?></h3>
    <?php
        use Cake\I18n\Time;
            
        $admin = $this->request->session()->read('is_admin');
        $supervisor = ( $this->request->session()->read('selected_project_role') == 'supervisor' ) ? 1 : 0;
        $manager = ( $this->request->session()->read('selected_project_role') == 'manager' ) ? 1 : 0;
        $developer = ( $this->request->session()->read('selected_project_role') == 'developer' ) ? 1 : 0;
        if($admin || $supervisor || $manager) { ?>
            <button id="navbutton"> <?= $this->Html->link(__('Log time for another member'), ['action' => 'adddev']) ?></button>
    <?php } else { ?>
    <?= $this->Form->create($workinghour) ?>
          <?php 
            /*
             * Req 1
             * Using jQuery UI datepicker
             * Added css and js files for datepicker to webroot
             * Changed settings for validation in WorkingHoursTable.php
             * Readonly turns the text field grey and doesn't allow other input than 
             * the date selected from the calendar
             * Added input[readonly] to cake.css
             */
          
            echo $this->Form->input('date', ['type' => 'text', 'readonly' => true]);
            ?> </br>
        <?php  
            echo $this->Form->input('description');
            echo $this->Form->input('duration', array('style' => 'width: 35%;'));
            echo $this->Form->input('worktype_id', ['options' => $worktypes, 'empty' => ' ', 'required' => true]); 
            
            /*
             * Req 1
             * If there are no weekly reports for the project then the minimum date 
             * in the datepicker's date range is the date the project was created.
             * Otherwise, the minimum date in the date range is the monday after
             * the last weekly report was sent.
             */
         
            $project_id = $this->request->session()->read('selected_project')['id'];
            /*
            $query1 = Cake\ORM\TableRegistry::get('Weeklyreports')
                ->find()
           	->select(['year','week']) 
            	->where(['project_id =' => $project_id])
                ->toArray(); 
            
            if ($query1 != null) {
                // picking out the week of the last weekly report from the results
                $max = max($query1);

                $maxYear = $max['year'];
                $maxWeek = $max['week'];
                
                //$mDate: the first day of the new weeklyreport week (monday)
                $monday = new DateTime();
                $monday->setISODate($maxYear,$maxWeek,8);
                $mDate1 = $monday->format('d M Y');
                $mDate = date('d M Y', strtotime($mDate1));
            }             
            /*
             * The date project was created is fetched from the db.
             */
            // else {  
                $project_id = $this->request->session()->read('selected_project')['id'];
                $queryP = Cake\ORM\TableRegistry::get('Projects')
                        ->find()
                        ->select(['created_on']) 
                        ->where(['id =' => $project_id])
                        ->toArray(); 

                foreach($queryP as $result) {
                    $temp = date_parse($result);
                    $year = $temp['year'];
                    $month = $temp['month'];
                    $day = $temp['day'];
                    // $mDate is the date project was created on      
                    $mDate = date("d M Y", mktime(0,0,0, $month, $day, $year));
                }
            // }    
	    echo $this->Form->button(__('Submit'));
        ?>    
    <?= $this->Form->end() ?>
    <?php } ?>
</div>

<div class="workinghours index large-9 medium-18 columns content float: left">
    <h3><?= __('Project team\'s logged tasks') ?></h3>
    <?php // the code for the menu is the same as in adddev.ctp 
    //echo $this->Form->input('member_id', ['options' => $members, 'label' => 'Show hours for', 'empty' => '']) . $this->Form->button(__('Submit'));
    ?>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width:130px;"><?= $this->Paginator->sort('member_id') ?></th>
                <th style="width:75px;"><?= $this->Paginator->sort('date') ?></th>
                <th style="width:60px;"><?= __('Week') ?></th>
                <th colspan="2"><?= __('Description') ?></th>
                <th style="width:65px;"><?= $this->Paginator->sort('duration') ?></th>
                <th><?= $this->Paginator->sort('worktype_id') ?></th>
                <th style="width:70px;" class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($workinghours as $workinghour): ?>
            <tr>
                <?php
                    foreach($memberlist as $member){
                        if($workinghour->member->id == $member['id']){
                           $workinghour->member['member_name'] = $member['member_name'];
                        }
                    }
                ?>
                <td ><?= $workinghour->has('member') ? $this->Html->link($workinghour->member->member_name, ['controller' => 'Workinghours', 'action' => 'tasks', $workinghour->member->id]) : '' ?></td>  
                <td><?= h($workinghour->date->format('d.m.Y')) ?></td>
                <td style="text-align: center;"><?= h($workinghour->date->format('W')) ?></td>
                <td colspan="2" style="font-family:monospace;"><?= h(wordwrap($workinghour->description,22,"\n",TRUE)) ?></td>
                <td style="text-align: center;"><?= $this->Number->format($workinghour->duration) ?></td>  
                <?php // link for admin, text for others
                if ($admin) { ?>
                    <td><?= $workinghour->has('worktype') ? $this->Html->link($workinghour->worktype->description, ['controller' => 'Worktypes', 'action' => 'view', $workinghour->worktype->id]) : '' ?></td>
                <?php } else { ?>
                    <td><?= h($workinghour->worktype->description) ?></td>
                <?php } ?>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $workinghour->id]) ?>
                    <?php
                    $admin = $this->request->session()->read('is_admin');
                    $supervisor = ( $this->request->session()->read('selected_project_role') == 'supervisor' ) ? 1 : 0;
                    $manager = ( $this->request->session()->read('selected_project_role') == 'manager' ) ? 1 : 0;
                    // the week and the year of the workinghour
                    /*
                     * 
<td ><?= $workinghour->has('member') ? $this->Html->link($workinghour->member->member_name, ['controller' => 'Members', 'action' => 'view', $workinghour->member->id]) : '' ?></td> 
                     * 
                     * 
                    $week= $workinghour->date->format('W');
                    $year= $workinghour->date->format('Y');
                    $firstWeeklyReport = false;
                    // the week and year of the last weekly report
                    $project_id = $this->request->session()->read('selected_project')['id'];
                    $query = Cake\ORM\TableRegistry::get('Weeklyreports')
                        ->find()
                        ->select(['year','week']) 
                        ->where(['project_id =' => $project_id])
                        ->toArray();
                    if ($query != null) {
                        // picking out the week of the last weeklyreport from the results
                        $max = max($query);
                        $maxYear = $max['year'];
                        $maxWeek = $max['week'];
                    }
                    else {
                        $firstWeeklyReport = true;
                    }
                    */
                    // edit and delete are only shown if the weekly report is not sent
                    // edit and delete can also be viewed by the developer who owns them
					
                        /* BUG FIX: admins couldn't see times that were old
                	 * Now it looks kinda complicated, but it means this:
			 * IF (you are the owning user or a manager) AND workinghour isn't from previous weeks
			 * OR you are an admin or a supervisor
			 */
                    //if (( ($workinghour->member->user_id == $this->request->session()->read('Auth.User.id') || $manager)
                    //        && ($firstWeeklyReport || (($year == $maxYear) && ($week > $maxWeek)) || ($year > $maxYear) )) 
                    //                          || ($admin || $supervisor) ) { 
                    if ( ($workinghour->member->user_id == $this->request->session()->read('Auth.User.id')) || $manager || $supervisor ||  $admin  ) { ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $workinghour->id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $workinghour->id], ['confirm' => __('Are you sure you want to delete # {0}?', $workinghour->id)]) ?> 
                    <?php } ?>
                </td>
            </tr>
            <?php endforeach; ?>
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

<script> 
    /*
     * minDate is the date project was created
     * maxDate is the current day
     * both the input field and the icon can be clicked
     */
    $( "#date" ).datepicker({
        dateFormat: "MM d, yy",
        minDate: new Date('<?php echo $mDate; ?>'),
        maxDate: '0', 
        firstDay: 1,
        showWeek: true,
        showOn: "both",
        buttonImage: "../webroot/img/glyphicons-46-calendar.png",
        buttonImageOnly: true,
        buttonText: "Select date"       
    });
  </script>