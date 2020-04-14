
<div class="workinghours form large-8 medium-16 columns content float: left">
    <h3><?= __('Edit risk') ?></h3>
    <?= $this->Form->create($risk) ?>
        <?php  
        echo $this->Form->input('description');
        if ($deletable) {
            echo $this->Form->input('impact', ['options' => $types, 'empty' => ' ', 'required' => true]); 
            echo $this->Form->input('probability', ['options' => $types, 'empty' => ' ', 'required' => true]);
        }
        
        $project_id = $this->request->session()->read('selected_project')['id'];
          
	    echo $this->Form->button(__('Submit'));
        ?>
    <?= $this->Form->end() ?>
</div>