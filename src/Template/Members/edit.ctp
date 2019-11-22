<?php
echo $this->Html->css('jquery-ui.min');
echo $this->Html->script('jquery');
echo $this->Html->script('jquery-ui.min');
?>


    <ul class="side-nav">
        <li><?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $member->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $member->id)]
            )
        ?></li>
    </ul>

<div class="members form large-8 medium-16 columns content float: left">
    <?= $this->Form->create($member) ?>
    <fieldset>
        <?php 
        $admin = $this->request->session()->read('is_admin');
        $supervisor = ( $this->request->session()->read('selected_project_role') == 'supervisor' ) ? 1 : 0;
        $userid = $member->user_id;
        $target_hours = 100;
        if ($member->target_hours != NULL) {
            $target_hours = $member->target_hours;
        }
        $queryName = Cake\ORM\TableRegistry::get('Users')
            ->find()
            ->select(['first_name','last_name'])          	
            ->where(['id =' => $userid])
            ->toArray(); 
            
            if ($queryName != null) { ?>
                <legend><?= __('Edit member: ') . $queryName[0]['first_name'] . " " . $queryName[0]['last_name'] ?></legend>    
            <?php } 
            
            if ($admin || $supervisor) {
            echo $this->Form->input('project_role', 
                ['options' => array('developer' => 'developer', 'manager' => 'manager', 'supervisor' => 'supervisor', 'client' => 'client')]);
            }

            echo $this->Form->input('target_hours', array('type' => 'integer', 'value' => $target_hours, 'style' => 'width: 15%;'));
     
            ?><div style="overflow: auto"><div class="columns medium-6 no-padding"><?php
            
            
            if ($admin || $supervisor) {
                // Using jQuery UI datepicker
                // Starting date
                Cake\I18n\Time::setToStringFormat('MMMM d, yyyy');
                echo $this->Form->input('starting_date', ['type' => 'text', 'readonly' => true, 'id' => 'datepicker1']);            
            
                // Ending date
                echo $this->Form->input('ending_date', ['type' => 'text', 'readonly' => true, 'id' => 'datepicker2']);
            ?>
            </div>
            <div class="columns medium-6 no-padding reset-buttons">
            
                <input type="button" value="Clear starting date" id="resetStart" /><br>
                <input type="button" value="Clear ending date" id="resetEnd" />

            </div></div>
            <?php
            }            
            ?> 
            
            
            <?php
            // Fetching from the db the date when the project was created          
            $project_id = $this->request->session()->read('selected_project')['id'];
            $query = Cake\ORM\TableRegistry::get('Projects')
                ->find()
                ->select(['created_on']) 
                ->where(['id =' => $project_id])
                ->toArray(); 
                
            foreach($query as $result) {
                $temp = date_parse($result);
                $year = $temp['year'];
                $month = $temp['month'];
                $day = $temp['day'];   
                $mDate = date("d M Y", mktime(0,0,0, $month, $day, $year));
            }
            echo $this->Form->button(__('Submit'));
            
            $isAdmin = $this->request->session()->read('is_admin');
    ?>           
    </fieldset>
    <?= $this->Form->end() ?>
</div>

<script>         
    // minDate is the date the project was created, for admin there is no min date
    // maxDate is the current day

       $( "#datepicker1" ).datepicker({
        dateFormat: "MM d, yy",
        minDate: <?php if($isAdmin) { ?> null<?php } else { ?> new Date('<?php echo $mDate; ?>') <?php } ?>,
        maxDate: '0', 
        firstDay: 1,
        showWeek: true,
        showOn: "both",
        buttonImage: "../../webroot/img/glyphicons-46-calendar.png",
        buttonImageOnly: true,
        buttonText: "Select date"       
    });
        $( "#datepicker2" ).datepicker({
        dateFormat: "MM d, yy",
        minDate: <?php if($isAdmin) { ?> null<?php } else { ?> new Date('<?php echo $mDate; ?>') <?php } ?>,
        firstDay: 1,
        showWeek: true,
        showOn: "both",
        buttonImage: "../../webroot/img/glyphicons-46-calendar.png",
        buttonImageOnly: true,
        buttonText: "Select date"       
    });
    
    // Resetting datepickers
        var date1 = $("input[id$='datepicker1']");
        var date2 = $("input[id$='datepicker2']");
        $("#resetStart").on('click', function(){
            date1.attr('value','');
                date1.each(function(){
                    $(this).datepicker('setDate', null); 
                }); 
    });
        $("#resetEnd").on('click', function(){
            date2.attr('value','');
                date2.each(function(){
                    $(this).datepicker('setDate', null); 
                });
     
    });
</script>
