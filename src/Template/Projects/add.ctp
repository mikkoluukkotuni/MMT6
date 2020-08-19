<?php
echo $this->Html->css('jquery-ui.min');
echo $this->Html->script('jquery');
echo $this->Html->script('jquery-ui.min');
?>

<!-- 
    If the lower navigation bar needed, links go here
    
    <ul class="side-nav">
        
    </ul>
-->
<div class="projects form large-8 medium-16 columns content float: left">
    <h3><?= __('Add Project') ?></h3>
    <?= $this->Form->create($project) ?>

        <?php
            // jQuery UI datepicker
                        
            echo $this->Form->input('project_name');
            echo $this->Form->input('created_on', ['label' => 'Starting Date', 'type' => 'text', 'readonly' => true, 'id' => 'datepicker1']);
            echo $this->Form->input('description');
            echo $this->Form->input('customer');
            echo $this->Form->input('is_public', array('label' => "This project is public"));
			echo $this->Form->button(__('Submit'));
        ?>
    <?= $this->Form->end() ?>
</div>
<script> 
       $( "#datepicker1" ).datepicker({
        dateFormat: 'MM d, yy',        
        minDate: '-6M',
        maxDate: '+6M', 
        firstDay: 1,
        showWeek: true,
        showOn: "both",
        buttonImage: "../../webroot/img/glyphicons-46-calendar.png",
        buttonImageOnly: true,
        buttonText: "Select date"       
    });
</script>
